import { createElement, Fragment } from '@wordpress/element'
import OptionsPanel from '../OptionsPanel'
import { capitalizeFirstLetter, optionWithDefault } from '../GenericOptionType'

import {
	useDeviceManagerState,
	useDeviceManagerActions,
} from '../../customizer/components/useDeviceManager'
import ResponsiveControls from '../../customizer/components/responsive-controls'

import { getValueFromInput } from '../helpers/get-value-from-input'

import deepEqual from 'deep-equal'

const Group = ({
	renderingChunk,
	value,
	onChange,
	onChangeMultiple,
	purpose,
}) =>
	renderingChunk.map((groupOption) => {
		const {
			label,
			options,
			id,
			attr = {},
			wrapperAttr = {},
			responsive = false,

			hasRevertButton,
			hasGroupRevertButton = false,
		} = groupOption
		const { currentView } = useDeviceManagerState()
		const { setDevice } = useDeviceManagerActions()

		const groupContents = (
			<OptionsPanel
				purpose={purpose}
				onChange={onChange}
				options={options}
				value={value}
				hasRevertButton={hasGroupRevertButton ? false : hasRevertButton}
			/>
		)

		const handleRevert = () => {
			const defaults = getValueFromInput(options, {})

			if (onChangeMultiple) {
				onChangeMultiple(defaults)
				return
			}

			// In customizer, the logic is prone to race conditions due to
			// multiple updates happening in sequence.
			Object.keys(defaults).reduce((previousPromise, nextChoice) => {
				return previousPromise.then(() => {
					return new Promise((r) => {
						setTimeout(() => {
							onChange(nextChoice, defaults[nextChoice])
							r()
						})
					})
				})
			}, Promise.resolve())
		}

		return (
			<div key={id} className="ct-controls-group" {...wrapperAttr}>
				{label && (
					<header>
						<label>{label}</label>

						{hasGroupRevertButton && (
							<button
								type="button"
								disabled={deepEqual(
									getValueFromInput(options, {}),
									getValueFromInput(options, {}, (id) => ({
										[id]: value[id],
									}))
								)}
								className="ct-revert"
								onClick={handleRevert}>
								<svg fill="currentColor" viewBox="0 0 35 35">
									<path d="M17.5,26L17.5,26C12.8,26,9,22.2,9,17.5v0C9,12.8,12.8,9,17.5,9h0c4.7,0,8.5,3.8,8.5,8.5v0C26,22.2,22.2,26,17.5,26z" />
									<polygon points="34.5,30.2 21.7,17.5 34.5,4.8 30.2,0.5 17.5,13.3 4.8,0.5 0.5,4.8 13.3,17.5 0.5,30.2 4.8,34.5 17.5,21.7 30.2,34.5 " />
								</svg>
							</button>
						)}

						{responsive && (
							<ResponsiveControls
								device={currentView}
								responsiveDescriptor={responsive}
								setDevice={setDevice}
							/>
						)}
					</header>
				)}
				<section
					{...attr}
					{...(currentView !== 'desktop'
						? { 'data-disabled-last': '' }
						: {})}>
					{groupContents}
				</section>
			</div>
		)
	})

export default Group
