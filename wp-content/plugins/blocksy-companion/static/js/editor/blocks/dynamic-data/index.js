import { createElement, useMemo } from '@wordpress/element'

import { __ } from 'ct-i18n'
import { registerBlockType } from '@wordpress/blocks'
import Edit from './Edit'

import metadata from './block.json'

import { getAttributesFromOptions, getOptionsForBlock } from 'blocksy-options'

export const options = getOptionsForBlock('dynamic-data')
export const defaultAttributes = getAttributesFromOptions(options)

registerBlockType('blocksy/dynamic-data', {
	...metadata,
	title: __('Dynamic Data', 'blocksy-companion'),
	description: __(
		'Fetch and display content from various sources.',
		'blocksy-companion'
	),
	attributes: {
		...metadata.attributes,
		...defaultAttributes,
	},
	icon: {
		src: (
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
				<path d="M17.9 10.5c-.1-.3-.4-.4-.7-.4h-3.7V4.6c0-.4-.2-.7-.6-.8h-.2c-.3 0-.5.1-.7.4l-5.7 8.6c-.2.3-.2.6 0 .8 0 .2.3.4.6.4h3.7v5.5c0 .4.2.7.6.8h.2c.3 0 .5-.1.7-.4l5.7-8.6c.2-.2.2-.6.1-.8zm-5.9 7v-4.4c0-.3-.3-.6-.6-.6H7.9l4.1-6v4.4c0 .3.3.6.6.6h3.5l-4.1 6z" />
			</svg>
		),
	},
	edit: (props) => <Edit {...props} />,
	save: () => null,
	__experimentalLabel: (attributes) => {
		if (attributes.field === 'wp:title') {
			return __('Dynamic Title', 'blocksy-companion')
		}

		if (attributes.field === 'wp:excerpt') {
			return __('Dynamic Excerpt', 'blocksy-companion')
		}

		if (attributes.field === 'wp:date') {
			return __('Dynamic Post Date', 'blocksy-companion')
		}

		if (attributes.field === 'wp:comments') {
			return __('Dynamic Comments', 'blocksy-companion')
		}

		if (attributes.field === 'wp:terms') {
			return __('Dynamic Terms', 'blocksy-companion')
		}

		if (attributes.field === 'wp:author') {
			return __('Dynamic Author', 'blocksy-companion')
		}

		if (attributes.field === 'wp:featured_image') {
			return __('Dynamic Featured Image', 'blocksy-companion')
		}

		if (attributes.field === 'wp:author_avatar') {
			return __('Dynamic Author Avatar', 'blocksy-companion')
		}

		if (attributes.field === 'woo:price') {
			return __('Dynamic Price', 'blocksy-companion')
		}

		if (attributes.field === 'woo:stock_status') {
			return __('Dynamic Stock Status', 'blocksy-companion')
		}

		if (attributes.field === 'woo:brands') {
			return __('Dynamic Brands', 'blocksy-companion')
		}

		if (attributes.field === 'woo:sku') {
			return __('Dynamic SKU', 'blocksy-companion')
		}

		if (attributes.field === 'woo:rating') {
			return __('Dynamic Rating', 'blocksy-companion')
		}

		if (attributes.field === 'wp:term_title') {
			return __('Dynamic Term Title', 'blocksy-companion')
		}

		if (attributes.field === 'wp:term_description') {
			return __('Dynamic Term Description', 'blocksy-companion')
		}

		if (attributes.field === 'wp:term_count') {
			return __('Dynamic Term Count', 'blocksy-companion')
		}

		if (attributes.field === 'wp:term_image') {
			return __('Dynamic Term Image', 'blocksy-companion')
		}

		if (attributes.field === 'wp:archive_image') {
			return __('Dynamic Archive Image', 'blocksy-companion')
		}

		if (attributes.field === 'wp:archive_title') {
			return __('Dynamic Archive Title', 'blocksy-companion')
		}

		if (attributes.field === 'wp:archive_description') {
			return __('Dynamic Archive Description', 'blocksy-companion')
		}

		return __('Dynamic Data', 'blocksy-companion')
	},
})
