import {
	createElement,
	memo,
	useEffect,
	useState,
	RawHTML,
} from '@wordpress/element'
import { __ } from 'ct-i18n'

import { list, grid } from '@wordpress/icons'

import classnames from 'classnames'

import { useSelect } from '@wordpress/data'
import { Panel, PanelBody, Spinner, ToolbarGroup } from '@wordpress/components'

import { useFlexySlider } from '../../hooks/use-flexy-slider'

import { getVariablesDefinitions } from './variables'

import { getStylesForBlock } from '../../utils/css'

import {
	InspectorControls,
	useInnerBlocksProps,
	BlockControls,
	BlockContextProvider,
	__experimentalUseBlockPreview as useBlockPreview,
	useBlockProps,
	store as blockEditorStore,
	BlockVerticalAlignmentToolbar,
	getSpacingPresetCssVar,
} from '@wordpress/block-editor'

import { usePostsBlockData } from '../query/hooks/use-posts-block-data'
import RangeControl from '../../components/RangeControl'

const TEMPLATE = [
	// ['core/post-title'],
	// ['core/post-date'],
	// ['core/post-excerpt'],
]

export function PostTemplateInnerBlocks({ isSlideshow, elementDescriptor }) {
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-post is-layout-flow' },
		{ template: TEMPLATE, __unstableDisableLayoutClassNames: true }
	)

	if (isSlideshow) {
		const { className, ...attr } = elementDescriptor?.attr || {}

		return (
			<div className={classnames('flexy-item', className)} {...attr}>
				<article {...innerBlocksProps} />
			</div>
		)
	}

	return <article {...innerBlocksProps} />
}

function PostTemplateBlockPreview({
	blocks,
	blockContextId,
	isHidden,
	isSlideshow,
	elementDescriptor,
	setActiveBlockContextId,
}) {
	const blockPreviewProps = useBlockPreview({
		blocks,
		props: {
			className: 'wp-block-post is-layout-flow',
		},
	})

	const handleOnClick = () => {
		setActiveBlockContextId(blockContextId)
	}

	if (isHidden) {
		return null
	}

	if (isSlideshow) {
		const { className, ...attr } = elementDescriptor?.attr || {}

		return (
			<div className={classnames('flexy-item', className)} {...attr}>
				<article
					tabIndex={0}
					{...blockPreviewProps}
					// eslint-disable-next-line jsx-a11y/no-noninteractive-element-to-interactive-role
					role="button"
					onClick={handleOnClick}
					onKeyPress={handleOnClick}
				/>
			</div>
		)
	}

	return (
		<article
			{...blockPreviewProps}
			tabIndex={0}
			// eslint-disable-next-line jsx-a11y/no-noninteractive-element-to-interactive-role
			role="button"
			onClick={handleOnClick}
			onKeyPress={handleOnClick}
		/>
	)
}

export const MemoizedPostTemplateBlockPreview = memo(PostTemplateBlockPreview)

export const getStylesCss = (args = {}) => {
	args = {
		selector: '',

		desktopCss: '',
		tabletCss: '',
		mobileCss: '',

		...args,
	}

	return `
		${args.selector} {
			${args.desktopCss}
		}

		@media (max-width: 999.98px) {
			${args.selector} {
				${args.tabletCss}
			}
		}

		@media (max-width: 689.98px) {
			${args.selector} {
				${args.mobileCss}
			}
		}
	`
}

export const SlideshowArrows = ({ has_slideshow_arrows, sliderDescriptor }) => {
	if (!has_slideshow_arrows) {
		return null
	}

	return (
		<>
			<span
				class="flexy-arrow-prev"
				{...sliderDescriptor.arrowsDescritor.left}>
				<svg
					width="16"
					height="10"
					fill="currentColor"
					viewBox="0 0 16 10">
					<path d="M15.3 4.3h-13l2.8-3c.3-.3.3-.7 0-1-.3-.3-.6-.3-.9 0l-4 4.2-.2.2v.6c0 .1.1.2.2.2l4 4.2c.3.4.6.4.9 0 .3-.3.3-.7 0-1l-2.8-3h13c.2 0 .4-.1.5-.2s.2-.3.2-.5-.1-.4-.2-.5c-.1-.1-.3-.2-.5-.2z"></path>
				</svg>
			</span>

			<span
				class="flexy-arrow-next"
				{...sliderDescriptor.arrowsDescritor.right}>
				<svg
					width="16"
					height="10"
					fill="currentColor"
					viewBox="0 0 16 10">
					<path d="M.2 4.5c-.1.1-.2.3-.2.5s.1.4.2.5c.1.1.3.2.5.2h13l-2.8 3c-.3.3-.3.7 0 1 .3.3.6.3.9 0l4-4.2.2-.2V5v-.3c0-.1-.1-.2-.2-.2l-4-4.2c-.3-.4-.6-.4-.9 0-.3.3-.3.7 0 1l2.8 3H.7c-.2 0-.4.1-.5.2z"></path>
				</svg>
			</span>
		</>
	)
}

const Edit = ({
	clientId,

	className,

	attributes,
	attributes: { layout, verticalAlignment },

	setAttributes,

	context,
	__unstableLayoutClassNames,
}) => {
	const { postId, has_slideshow, has_slideshow_arrows, uniqueId } = context

	const [activeBlockContextId, setActiveBlockContextId] = useState()

	const { type: layoutType, columnCount = 3 } = layout || {}
	const isGridLayout = layoutType === 'grid'
	const isManualMode = columnCount !== null

	const isSlideshow = has_slideshow === 'yes'

	const blockStyles = getStylesForBlock(
		getVariablesDefinitions({ attributes, context })
	)

	const blockProps = useBlockProps({
		className: classnames(__unstableLayoutClassNames, {
			'ct-query-template-grid': isGridLayout && !isSlideshow,
			'ct-query-template-default': !isGridLayout && !isSlideshow,
			'is-layout-flow': !isGridLayout && !isSlideshow,
			'ct-query-template': isSlideshow,
			'is-layout-slider': isSlideshow,
		}),

		'data-id': uniqueId,
	})

	const { blockData } = usePostsBlockData({
		attributes: context,
		previewedPostId: postId,
	})

	const sliderDescriptor = useFlexySlider({
		isSlideshow,
		context,
		attributes,
		toWatch: blockData ? blockData.all_posts : {},
	})

	const { blocks } = useSelect(
		(select) => {
			const { getBlocks } = select(blockEditorStore)

			return {
				blocks: getBlocks(clientId),
			}
		},
		[clientId]
	)

	if (!blockData) {
		return (
			<p {...blockProps}>
				<Spinner />
			</p>
		)
	}

	let blockContexts = blockData.all_posts.map((post) => ({
		postId: post.ID,
		postType: post.post_type,
	}))

	const displayLayoutControls = [
		{
			icon: list,
			title: __('List view'),
			onClick: () => {
				setAttributes({
					layout: {
						type: 'default',
					},
					tabletColumns: 2,
					mobileColumns: 1,
				})
			},
			isActive: !isGridLayout,
		},

		{
			icon: grid,
			title: __('Grid view'),
			onClick: () => {
				setAttributes({
					layout: {
						type: 'grid',
						columnCount: 3,
					},
					tabletColumns: 2,
					mobileColumns: 1,
				})
			},
			isActive: isGridLayout,
		},
	]

	const getItems = ({ isSlideshow, elementDescriptorForIndex }) => (
		<>
			{blockContexts.length === 0 && (
				<p>{__('No results found.', 'blocksy-companion')}</p>
			)}

			{blockContexts.length > 0 &&
				blockContexts.map((blockContext, index) => {
					return (
						<BlockContextProvider
							key={blockContext.postId}
							value={blockContext}>
							{blockContext.postId ===
							(activeBlockContextId ||
								blockContexts[0]?.postId) ? (
								<PostTemplateInnerBlocks
									isSlideshow={isSlideshow}
									elementDescriptor={
										elementDescriptorForIndex
											? elementDescriptorForIndex(index)
											: null
									}
								/>
							) : null}

							<MemoizedPostTemplateBlockPreview
								blocks={blocks}
								blockContextId={blockContext.postId}
								setActiveBlockContextId={
									setActiveBlockContextId
								}
								isSlideshow={isSlideshow}
								elementDescriptor={
									elementDescriptorForIndex
										? elementDescriptorForIndex(index)
										: null
								}
								isHidden={
									blockContext.postId ===
									(activeBlockContextId ||
										blockContexts[0]?.postId)
								}
							/>
						</BlockContextProvider>
					)
				})}
		</>
	)

	return (
		<>
			<BlockControls>
				<ToolbarGroup controls={displayLayoutControls} />

				{isGridLayout ? (
					<BlockVerticalAlignmentToolbar
						onChange={(newVerticalAlignment) => {
							setAttributes({
								verticalAlignment: newVerticalAlignment,
							})
						}}
						value={verticalAlignment}
					/>
				) : null}
			</BlockControls>

			<InspectorControls>
				{isGridLayout && isManualMode ? (
					<PanelBody title="Layout" initialOpen>
						<RangeControl
							label={__('Desktop Columns', 'blocksy-companion')}
							onChange={(columns) =>
								setAttributes({
									layout: {
										...layout,
										columnCount: columns,
									},
								})
							}
							value={columnCount}
						/>
						<RangeControl
							label={__('Tablet Columns', 'blocksy-companion')}
							onChange={(columns) =>
								setAttributes({
									tabletColumns: columns,
								})
							}
							value={attributes?.tabletColumns}
						/>
						<RangeControl
							label={__('Mobile Columns', 'blocksy-companion')}
							onChange={(columns) =>
								setAttributes({
									mobileColumns: columns,
								})
							}
							value={attributes?.mobileColumns}
						/>
					</PanelBody>
				) : null}
			</InspectorControls>

			<div {...blockProps}>
				{!isSlideshow ? (
					getItems({ isSlideshow })
				) : (
					<div
						class="flexy-container"
						data-flexy="no"
						{...sliderDescriptor.flexyContainerAttr}>
						<div class="flexy">
							<div class="flexy-view" data-flexy-view="boxed">
								<div class="flexy-items">
									{getItems({
										isSlideshow,
										elementDescriptorForIndex:
											sliderDescriptor.elementDescriptorForIndex,
									})}
								</div>
							</div>

							<SlideshowArrows
								has_slideshow_arrows={
									has_slideshow_arrows === 'yes'
								}
								sliderDescriptor={sliderDescriptor}
							/>
						</div>
					</div>
				)}
			</div>

			{blockData && context.has_pagination === 'yes' && !isSlideshow && (
				<RawHTML>{blockData.pagination_output}</RawHTML>
			)}

			{blockStyles ? blockStyles : null}
		</>
	)
}

export default Edit
