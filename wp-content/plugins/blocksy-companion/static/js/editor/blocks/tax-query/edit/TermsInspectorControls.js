import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'

import { InspectorControls } from '@wordpress/block-editor'
import { OptionsPanel } from 'blocksy-options'
import BlocksyToolsPanel from '../../../components/ToolsPanel'
import { useTaxonomyLayers } from '../../query/edit/layers/useTaxonomiesLayers'

const TermsInspectorControls = ({
	context,
	attributes,
	attributes: { post_type },
	setAttributes,
}) => {
	const { taxonomiesGroup } = useTaxonomyLayers({
		attributes,
		setAttributes,
	})

	return (
		<InspectorControls>
			<BlocksyToolsPanel
				className="ct-query-parameters-component"
				attributes={attributes}
				setAttributes={setAttributes}
				resetAll={() => {
					setAttributes({
						offset: 0,
						orderby: 'none',
						order: 'desc',

						include_term_ids: {},
						exclude_term_ids: {},
					})
				}}
				items={[
					{
						label: __('General', 'blocksy-companion'),
						items: [
							{
								label: __('Offset', 'blocksy-companion'),

								hasValue: () => {
									return attributes.offset !== 0
								},

								reset: () => {
									setAttributes({
										offset: 0,
									})
								},

								render: () => {
									return (
										<OptionsPanel
											purpose="gutenberg"
											onChange={(
												optionId,
												optionValue
											) => {
												setAttributes({
													[optionId]: optionValue,
												})
											}}
											options={{
												offset: {
													type: 'ct-number',
													label: __(
														'Offset',
														'blocksy-companion'
													),
													value: '',
													min: 0,
													max: 500,
												},
											}}
											value={attributes}
											hasRevertButton={false}
										/>
									)
								},
							},

							{
								label: __('Order by', 'blocksy-companion'),

								hasValue: () => {
									return attributes.orderby !== 'none'
								},

								reset: () => {
									setAttributes({
										orderby: 'none',
									})
								},

								render: () => {
									return (
										<OptionsPanel
											purpose="gutenberg"
											onChange={(
												optionId,
												optionValue
											) => {
												setAttributes({
													[optionId]: optionValue,
												})
											}}
											options={{
												orderby: {
													type: 'ct-select',
													label: __(
														'Order by',
														'blocksy-companion'
													),
													value: '',
													choices: [
														{
															key: 'id',
															value: __(
																'ID',
																'blocksy-companion'
															),
														},

														{
															key: 'name',
															value: __(
																'Name',
																'blocksy-companion'
															),
														},

														{
															key: 'count',
															value: __(
																'Count',
																'blocksy-companion'
															),
														},

														{
															key: 'rand',
															value: __(
																'Random',
																'blocksy-companion'
															),
														},
													],
												},
											}}
											value={attributes}
											hasRevertButton={false}
										/>
									)
								},
							},

							{
								label: __('Order', 'blocksy-companion'),

								hasValue: () => {
									return attributes.order !== 'desc'
								},

								reset: () => {
									setAttributes({
										order: 'desc',
									})
								},

								render: () => {
									return (
										<OptionsPanel
											purpose="gutenberg"
											onChange={(
												optionId,
												optionValue
											) => {
												setAttributes({
													[optionId]: optionValue,
												})
											}}
											options={{
												order: {
													type: 'ct-select',
													label: __(
														'Order',
														'blocksy-companion'
													),
													value: '',
													choices: [
														{
															key: 'DESC',
															value: __(
																'Descending',
																'blocksy-companion'
															),
														},

														{
															key: 'ASC',
															value: __(
																'Ascending',
																'blocksy-companion'
															),
														},
													],
												},
											}}
											value={attributes}
											hasRevertButton={false}
										/>
									)
								},
							},
						],
					},

					...(taxonomiesGroup ? [taxonomiesGroup] : []),
				]}
				label={__('Parameters', 'blocksy-companion')}
			/>
		</InspectorControls>
	)
}

export default TermsInspectorControls
