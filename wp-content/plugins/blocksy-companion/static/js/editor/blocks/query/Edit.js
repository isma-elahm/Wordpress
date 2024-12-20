import { useState, createElement, useRef, useEffect } from '@wordpress/element'
import { __ } from 'ct-i18n'

import { TextControl, ToolbarGroup, ToolbarButton } from '@wordpress/components'
import { dispatch, useSelect, useDispatch } from '@wordpress/data'

import {
	InspectorControls,
	useBlockProps,
	BlockControls,
	withColors,
	useInnerBlocksProps,
	store as blockEditorStore,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'
import Preview from './Preview'
import ColorsPanel from '../../components/ColorsPanel'

import { OptionsPanel, mountFlexy } from 'blocksy-options'

import { PanelBody } from '@wordpress/components'

import { useUniqueId } from '../../hooks/use-unique-id'

import PostsPlaceholder from './edit/PostsPlaceholder'
import PostsInspectorControls from './edit/PostsInspectorControls'
import PatternSelectionModal from './edit/PatternSelectionModal'

import { usePostTypes } from './edit/utils/utils'

const Edit = ({
	clientId,

	className,

	attributes,
	setAttributes,

	context,
}) => {
	const innerBlocksProps = useInnerBlocksProps({}, {})

	const hasInnerBlocks = useSelect(
		(select) => !!select(blockEditorStore).getBlocks(clientId).length,
		[clientId]
	)

	const isOnboarding = !hasInnerBlocks && attributes.design !== 'default'

	const { postTypesSelectOptions } = usePostTypes({
		hasPages: hasInnerBlocks,
	})

	const { uniqueId, props: wrapperProps } = useUniqueId({
		attributes,
		setAttributes,
		clientId,
	})

	const { postId, postType } = context

	const navRef = useRef()

	const [flexyInstance, setFlexyInstance] = useState(null)

	const borderProps = useBorderProps(attributes)

	const blockProps = useBlockProps({
		ref: navRef,
		className,
		style: {
			...borderProps.style,
		},
	})

	const [isPatternSelectionModalOpen, setIsPatternSelectionModalOpen] =
		useState(false)

	return (
		<>
			{isPatternSelectionModalOpen && (
				<PatternSelectionModal
					clientId={clientId}
					attributes={attributes}
					setIsPatternSelectionModalOpen={
						setIsPatternSelectionModalOpen
					}
					postType={attributes.post_type}
				/>
			)}

			{!isOnboarding ? (
				<div {...blockProps} {...wrapperProps}>
					{attributes.design === 'default' && (
						<Preview
							uniqueId={uniqueId}
							attributes={attributes}
							postId={postId}
						/>
					)}

					{hasInnerBlocks && <div {...innerBlocksProps} />}
				</div>
			) : (
				<div {...blockProps} {...wrapperProps}>
					<PostsPlaceholder
						setIsPatternSelectionModalOpen={
							setIsPatternSelectionModalOpen
						}
						attributes={attributes}
						setAttributes={setAttributes}
						clientId={clientId}
					/>
				</div>
			)}

			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						className="components-toolbar__control"
						icon="layout"
						label={__('Reset layout', 'blocksy-companion')}
						disabled={isOnboarding}
						onClick={() => {
							if (hasInnerBlocks) {
								dispatch(
									'core/block-editor'
								).replaceInnerBlocks(clientId, [], false)
							} else {
								dispatch(
									'core/block-editor'
								).updateBlockAttributes(clientId, {
									design: '',
								})
							}
						}}
					/>
				</ToolbarGroup>
			</BlockControls>

			{!isOnboarding && (
				<>
					<InspectorControls>
						<PanelBody>
							<OptionsPanel
								purpose="gutenberg"
								onChange={(optionId, optionValue) => {
									setAttributes({
										[optionId]: optionValue,
										...(optionId === 'post_type'
											? {
													include_term_ids: {},
													exclude_term_ids: {},

													include_post_ids: {
														ids: [],
														current_post: false,
													},

													exclude_post_ids: {
														ids: [],
														current_post: false,
													},
											  }
											: {}),
									})
								}}
								options={{
									post_type: {
										type: 'ct-select',
										label: __(
											'Post Type',
											'blocksy-companion'
										),
										value: '',
										defaultToFirstItem: false,
										choices: postTypesSelectOptions,
										purpose: 'default',
									},

									limit: {
										type: 'ct-number',
										label: __('Limit', 'blocksy-companion'),
										value: '',
										min: 1,
										max: 100,
									},

									has_slideshow: {
										type: 'ct-switch',
										label: __(
											'Slideshow',
											'blocksy-companion'
										),
										value: '',
									},

									has_slideshow_condition: {
										type: 'ct-condition',
										condition: { has_slideshow: 'yes' },
										options: {
											has_slideshow_arrows: {
												type: 'ct-switch',
												label: __(
													'Arrows',
													'blocksy-companion'
												),
												value: '',
											},

											has_slideshow_autoplay: {
												type: 'ct-switch',
												label: __(
													'Autoplay',
													'blocksy-companion'
												),
												value: '',
											},

											has_slideshow_autoplay_condition: {
												type: 'ct-condition',
												condition: {
													has_slideshow_autoplay:
														'yes',
												},
												options: {
													has_slideshow_autoplay_speed:
														{
															type: 'ct-number',
															label: __(
																'Autoplay Speed',
																'blocksy-companion'
															),
															value: '',
															min: 1,
															max: 100,
														},
												},
											},
										},
									},

									has_pagination_condition: {
										type: 'ct-condition',
										condition: { has_slideshow: 'no' },
										options: {
											has_pagination: {
												type: 'ct-switch',
												label: __(
													'Pagination',
													'blocksy-companion'
												),
												value: '',
											},
										},
									},
								}}
								value={attributes}
								hasRevertButton={false}
							/>
						</PanelBody>
					</InspectorControls>

					<PostsInspectorControls
						attributes={attributes}
						setAttributes={setAttributes}
						context={context}
					/>
				</>
			)}

			<InspectorControls group="advanced">
				<TextControl
					__nextHasNoMarginBottom
					autoComplete="off"
					label={__('Block ID', 'blocksy-companion')}
					value={uniqueId}
					onChange={(nextValue) => {}}
					onFocus={(e) => {
						e.target.select()
					}}
					help={__(
						'Please look at the documentation for more information on why this is useful.',
						'blocksy-companion'
					)}
				/>
			</InspectorControls>
		</>
	)
}

export default Edit
