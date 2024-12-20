import {
	createElement,
	useState,
	useContext,
	Fragment,
} from '@wordpress/element'
import { DragDropContext } from '../BuilderRoot'
import cls from 'classnames'
import Panel, { PanelMetaWrapper } from '../../../../options/options/ct-panel'
import { getValueFromInput } from '../../../../options/helpers/get-value-from-input'

import { __ } from 'ct-i18n'
import Overlay from '../../../components/Overlay'

const PanelsEmptyOverlay = ({ isShowing, setIsShowing }) => {
	return (
		<Overlay
			items={isShowing}
			className="ct-admin-modal ct-reset-options"
			onDismiss={() => setIsShowing(false)}
			render={() => (
				<div className="ct-modal-content">
					<h2 className="ct-modal-title">
						{__('Action Required!', 'blocksy')}
					</h2>
					<p>
						{__(
							'Please install and activate the Blocksy Companion plugin to get access to all transparent and sticky header features.',
							'blocksy'
						)}
					</p>

					<div
						className="ct-modal-actions has-divider"
						data-buttons="2">
						<button onClick={(e) => {}} className="button">
							{__('Cancel', 'blocksy')}
						</button>

						<a
							href={`${ct_customizer_localizations.wp_admin_url}/plugins.php`}
							className="button button-primary"
							onClick={(e) => {}}>
							{__('Go to plugins', 'blocksy')}
						</a>
					</div>
				</div>
			)}
		/>
	)
}

const PanelsManager = () => {
	const [isShowing, setIsShowing] = useState(false)

	const secondaryItems =
		ct_customizer_localizations.header_builder_data.secondary_items.header
	const allItems = ct_customizer_localizations.header_builder_data.header

	const {
		builderValue,
		option,
		builderValueCollection,
		builderValueDispatch,
		panelsActions,
	} = useContext(DragDropContext)

	const allSections = builderValueCollection.sections.filter(
		({ id }) =>
			id !== 'type-2' && id !== 'type-3' && id.indexOf('ct-custom') === -1
	)

	return (
		<ul className={cls('ct-panels-manager')}>
			{allSections.map(({ name, id }) => {
				let panelLabel =
					name ||
					{
						'type-1': __('Global Header', 'blocksy'),
					}[id] ||
					id

				const panelId = `builder_header_panel_${id}`

				const headerOptions =
					ct_customizer_localizations.header_builder_data.header_data
						.header_options

				const option = {
					label: panelLabel,
					'inner-options': headerOptions,
				}

				return (
					<Fragment>
						<PanelsEmptyOverlay
							isShowing={isShowing}
							setIsShowing={setIsShowing}
						/>

						<PanelMetaWrapper
							id={panelId}
							key={id}
							option={option}
							{...panelsActions}
							getActualOption={({ open }) => (
								<Fragment>
									{Object.keys(headerOptions).length > 0 &&
										id === builderValue.id && (
											<Panel
												id={panelId}
												getValues={() =>
													builderValue.settings || {}
												}
												option={option}
												onChangeFor={(
													optionId,
													optionValue
												) => {
													builderValueDispatch({
														type: 'BUILDER_GLOBAL_SETTING_ON_CHANGE',
														payload: {
															optionId,
															optionValue,
															values: getValueFromInput(
																headerOptions,
																Array.isArray(
																	builderValue.settings
																)
																	? {}
																	: builderValue.settings ||
																			{}
															),
														},
													})
												}}
												view="simple"
											/>
										)}

									{id === builderValue.id && (
										<li
											className={cls({
												active: id === builderValue.id,
												'ct-global': id === 'type-1',
											})}
											onClick={() => {
												if (
													Object.keys(headerOptions)
														.length > 0
												) {
													open()
												} else {
													setIsShowing(true)
												}
											}}>
											<span className="ct-panel-name">
												{panelLabel}
											</span>
										</li>
									)}
								</Fragment>
							)}
						/>
					</Fragment>
				)
			})}
		</ul>
	)
}

export default PanelsManager
