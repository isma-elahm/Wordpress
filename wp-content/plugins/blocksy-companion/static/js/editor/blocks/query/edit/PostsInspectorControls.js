import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'

import { InspectorControls } from '@wordpress/block-editor'

import { OptionsPanel } from 'blocksy-options'

import BlocksyToolsPanel from '../../../components/ToolsPanel'

import { useTaxonomiesLayers } from './layers/useTaxonomiesLayers'

const PostsInspectorControls = ({
	context,
	attributes,
	attributes: { post_type },
	setAttributes,
}) => {
	const { taxonomiesGroup } = useTaxonomiesLayers({
		attributes,
		setAttributes,
		previewedPostMatchesType: attributes.post_type === context.postType,
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
						sticky_posts: 'include',
						orderby: 'post_date',
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
									return attributes.orderby !== 'post_date'
								},

								reset: () => {
									setAttributes({
										orderby: 'post_date',
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
															key: 'title',
															value: __(
																'Title',
																'blocksy-companion'
															),
														},

														{
															key: 'post_date',
															value: __(
																'Publish Date',
																'blocksy-companion'
															),
														},

														{
															key: 'modified',
															value: __(
																'Modified Date',
																'blocksy-companion'
															),
														},

														{
															key: 'comment_count',
															value: __(
																'Most commented',
																'blocksy-companion'
															),
														},

														{
															key: 'author',
															value: __(
																'Author',
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

														{
															key: 'menu_order',
															value: __(
																'Menu Order',
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

							{
								label: __('Sticky Posts', 'blocksy-companion'),

								hasValue: () => {
									return attributes.sticky_posts !== 'include'
								},

								reset: () => {
									setAttributes({
										sticky_posts: 'include',
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
												sticky_posts: {
													type: 'ct-select',
													label: __(
														'Sticky Posts',
														'blocksy-companion'
													),
													value: 'include',
													choices: [
														{
															key: 'include',
															value: __(
																'Include',
																'blocksy-companion'
															),
														},

														{
															key: 'exclude',
															value: __(
																'Exclude',
																'blocksy-companion'
															),
														},

														{
															key: 'only',
															value: __(
																'Only',
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

export default PostsInspectorControls
