import { createElement } from '@wordpress/element'
import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor'
import { PanelBody, ToggleControl } from '@wordpress/components'

import { __ } from 'ct-i18n'

const Edit = ({ attributes, setAttributes, clientId, ...rest }) => {
	const isBlockLocation =
		!window.location.pathname.includes('widgets.php') &&
		!window.location.pathname.includes('customize.php')

	const currentBlock = wp.data.select('core/block-editor').getBlock(clientId)
	const collapsible =
		attributes.block === 'blocksy/woocommerce-filters' &&
		currentBlock.innerBlocks &&
		currentBlock.innerBlocks.length > 1 &&
		currentBlock.innerBlocks[0].name === 'core/heading'

	const template = [
		[
			'core/heading',
			{
				level: 3,
				content: attributes.heading || '',
				fontSize: 'medium',
				className: isBlockLocation ? '' : 'widget-title',
			},
		],
	]

	let allowedBlocks = ['core/heading']

	// Don't allow multiple headings
	if (currentBlock.innerBlocks.find(({ name }) => name === 'core/heading')) {
		allowedBlocks = []
	}

	if (attributes.hasDescription) {
		if (attributes.description) {
			template.push([
				'core/paragraph',
				{
					content: attributes.description,
					placeholder: 'Description',
				},
			])
		}

		allowedBlocks.push('core/paragraph')
	}

	template.push([
		attributes.block,
		{
			lock: {
				remove: true,
			},
			...attributes.blockAttrs,
		},
	])

	const blockProps = useBlockProps({
		className: {
			'wp-block-blocksy-widgets-wrapper--collapsible':
				attributes.isCollapsible,
			'wp-block-blocksy-widgets-wrapper--expanded':
				attributes.defaultExpanded,
		},
	})

	return (
		<>
			{collapsible ? (
				<InspectorControls>
					<PanelBody>
						<ToggleControl
							label={__(
								'Expandable Container',
								'blocksy-companion'
							)}
							checked={attributes.isCollapsible}
							onChange={() =>
								setAttributes({
									isCollapsible: !attributes.isCollapsible,
								})
							}
						/>

						{attributes.isCollapsible ? (
							<ToggleControl
								label={__(
									'Expanded by Default',
									'blocksy-companion'
								)}
								checked={attributes.defaultExpanded}
								onChange={() =>
									setAttributes({
										defaultExpanded:
											!attributes.defaultExpanded,
									})
								}
							/>
						) : null}
					</PanelBody>
				</InspectorControls>
			) : null}

			<div {...blockProps}>
				<InnerBlocks
					allowedBlocks={allowedBlocks}
					template={template}
				/>
			</div>
		</>
	)
}

export default Edit
