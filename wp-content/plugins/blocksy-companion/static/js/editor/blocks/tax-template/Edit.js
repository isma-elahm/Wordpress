import { createElement, memo, useState, RawHTML } from '@wordpress/element'
import { __ } from 'ct-i18n'

import { list, grid, desktop } from '@wordpress/icons'

import classnames from 'classnames'
import { useSelect } from '@wordpress/data'
import { Spinner, ToolbarGroup, PanelBody } from '@wordpress/components'
import { useFlexySlider } from '../../hooks/use-flexy-slider'

import {
	useInnerBlocksProps,
	BlockControls,
	BlockContextProvider,
	__experimentalUseBlockPreview as useBlockPreview,
	BlockVerticalAlignmentToolbar,
	useBlockProps,
	store as blockEditorStore,
	InspectorControls,
	getSpacingPresetCssVar,
} from '@wordpress/block-editor'

import { useTaxBlockData } from '../tax-query/hooks/use-tax-block-data'
import RangeControl from '../../components/RangeControl'
import { SlideshowArrows } from '../post-template/Edit'
import { getVariablesDefinitions } from './variables'

import { getStylesForBlock } from '../../utils/css'

const TEMPLATE = [
	// ['core/post-title'],
	// ['core/post-date'],
	// ['core/post-excerpt'],
]

export function TaxTemplateInnerBlocks({ isSlideshow, elementDescriptor }) {
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-term is-layout-flow' },
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

	return <div {...innerBlocksProps} />
}

function TaxTemplateBlockPreview({
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
			className: 'wp-block-term is-layout-flow',
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
				<div
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
		<div
			{...blockPreviewProps}
			tabIndex={0}
			// eslint-disable-next-line jsx-a11y/no-noninteractive-element-to-interactive-role
			role="button"
			onClick={handleOnClick}
			onKeyPress={handleOnClick}
		/>
	)
}

export const MemoizedTaxTemplateBlockPreview = memo(TaxTemplateBlockPreview)

const Edit = ({
	clientId,

	attributes: { layout, verticalAlignment, style },
	attributes,

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

	const { blockData } = useTaxBlockData({
		attributes: context,
		previewedPostId: postId,
	})

	const sliderDescriptor = useFlexySlider({
		isSlideshow,
		attributes,
		context,
		toWatch: blockData ? blockData.all_terms : {},
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

	let blockContexts = blockData.all_terms.map((term) => ({
		termId: term.term_id,
		termIcon: term?.icon,
		termImage: term?.image,
	}))

	const setDisplayLayout = (newDisplayLayout) =>
		setAttributes({
			layout: { ...layout, ...newDisplayLayout },
		})

	const displayLayoutControls = [
		{
			icon: list,
			title: __('List view'),
			onClick: () => setDisplayLayout({ type: 'default' }),
			isActive: layoutType === 'default' || layoutType === 'constrained',
		},
		{
			icon: grid,
			title: __('Grid view'),
			onClick: () =>
				setDisplayLayout({
					type: 'grid',
					columnCount,
				}),
			isActive: isGridLayout,
		},
	]

	const getItems = ({ isSlideshow, elementDescriptorForIndex }) => (
		<>
			{blockContexts.length === 0 && (
				<p>{__('No results found.', 'blocksy-companion')}</p>
			)}

			{blockContexts.length > 0 &&
				blockContexts.map((blockContext, index) => (
					<BlockContextProvider
						key={blockContext.termId}
						value={blockContext}>
						{blockContext.termId ===
						(activeBlockContextId || blockContexts[0]?.termId) ? (
							<TaxTemplateInnerBlocks
								isSlideshow={isSlideshow}
								elementDescriptor={
									elementDescriptorForIndex
										? elementDescriptorForIndex(index)
										: null
								}
							/>
						) : null}

						<MemoizedTaxTemplateBlockPreview
							blocks={blocks}
							blockContextId={blockContext.termId}
							setActiveBlockContextId={setActiveBlockContextId}
							isSlideshow={isSlideshow}
							elementDescriptor={
								elementDescriptorForIndex
									? elementDescriptorForIndex(index)
									: null
							}
							isHidden={
								blockContext.termId ===
								(activeBlockContextId ||
									blockContexts[0]?.termId)
							}
						/>
					</BlockContextProvider>
				))}
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
				{isGridLayout ? (
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

			{blockStyles ? blockStyles : null}
		</>
	)
}

export default Edit
