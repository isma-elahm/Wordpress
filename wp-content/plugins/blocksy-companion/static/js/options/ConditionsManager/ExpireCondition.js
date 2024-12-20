import {
	createPortal,
	useState,
	useRef,
	Fragment,
	createElement,
} from '@wordpress/element'
import classnames from 'classnames'
import { __ } from 'ct-i18n'

import {
	DateTimePicker,
	usePopoverMaker,
	OutsideClickHandler,
} from 'blocksy-options'

const ExpireCondition = ({ onChange, condition }) => {
	const [currentTab, setCurrentTab] = useState('start')
	const inputRef = useRef()
	const [isOpen, setIsOpen] = useState(false)

	const { styles, popoverProps } = usePopoverMaker({
		ref: inputRef,
		defaultHeight: 228,
		shouldCalculate: true,
	})

	const start = condition.payload.start || new Date()
	const end = condition.payload.end || new Date()

	let preview = [wp.date.format('j/n/Y', start), wp.date.format('j/n/Y', end)]

	if (preview[0] === preview[1]) {
		preview = [
			wp.date.format(wp.date.getSettings().formats.time, start),
			wp.date.format(wp.date.getSettings().formats.time, end),
		]
	}

	return (
		<Fragment>
			<OutsideClickHandler
				className="ct-select-input"
				wrapperProps={{
					ref: inputRef,
					role: 'combobox',
					'aria-expanded': isOpen,
					'aria-haspopup': 'listbox',
					onClick: () => {
						setIsOpen(!isOpen)
					},
				}}
				onOutsideClick={(e) => {
					if (e.target.closest('.ct-select-dropdown')) {
						return
					}

					setIsOpen(false)
				}}>
				<input
					value={preview.join(' - ')}
					onChange={() => {}}
					readOnly
				/>
			</OutsideClickHandler>

			{isOpen &&
				createPortal(
					<div
						className={classnames(
							'ct-select-dropdown ct-date-time-picker-dropdown',
							{
								'ct-fixed': true,
							}
						)}
						{...popoverProps}
						style={styles}>
						<div className="ct-start-end-date-switcher components-button-group">
							{['start', 'end'].map((tab) => {
								return (
									<button
										key={tab}
										className={classnames(
											'components-button',
											{
												'is-primary':
													tab === currentTab,
											}
										)}
										onClick={() => {
											setCurrentTab(tab)
										}}>
										{tab === 'start'
											? 'Start Date'
											: 'Expiration Date'}
									</button>
								)
							})}
						</div>

						<DateTimePicker
							value={
								(condition.payload || {})[currentTab] ||
								new Date()
							}
							onChange={(date) => {
								if (
									currentTab === 'end' &&
									new Date(date).getTime() <
										(condition.payload.start
											? new Date(
													condition.payload.start
											  ).getTime()
											: new Date().getTime())
								) {
									date = condition.payload.start

									alert(
										__(
											'The expiration date cannot be set earlier than the start date.',
											'blocksy-companion'
										)
									)
								}

								if (
									currentTab === 'start' &&
									new Date(date).getTime() >
										(condition.payload.end
											? new Date(
													condition.payload.end
											  ).getTime()
											: new Date().getTime())
								) {
									condition.payload.end = date
								}

								onChange({
									...condition,
									payload: {
										...condition.payload,
										...(!condition.payload.start
											? { start }
											: {}),
										[currentTab]: date,
									},
								})
							}}
						/>
					</div>,

					document.body
				)}
		</Fragment>
	)
}

export default ExpireCondition
