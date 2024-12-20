import { useState, useEffect } from '@wordpress/element'
import { __ } from 'ct-i18n'
import { getOptionsForBlock } from 'blocksy-options'
import cachedFetch from 'ct-wordpress-helpers/cached-fetch'

import { getLabelForProvider } from '../utils'
import { useTaxonomies } from '../../query/edit/utils/utils'

const options = getOptionsForBlock('dynamic-data')

const wpFields = (args = {}) => {
	const { termId } = args

	const isContentBlock = document.body.classList.contains(
		'post-type-ct_content_block'
	)

	let fields = []

	if (termId) {
		fields = [
			{
				id: 'term_title',
				label: __('Term Title', 'blocksy-companion'),
			},
			{
				id: 'term_description',
				label: __('Term Description', 'blocksy-companion'),
			},
			{
				id: 'term_image',
				label: __('Term Image', 'blocksy-companion'),
			},
			{
				id: 'term_count',
				label: __('Term Count', 'blocksy-companion'),
			},
		]
	}

	if (!termId) {
		fields = [
			{
				id: 'title',
				label: __('Title', 'blocksy-companion'),
			},

			{
				id: 'excerpt',
				label: __('Excerpt', 'blocksy-companion'),
			},

			{
				id: 'date',
				label: __('Post Date', 'blocksy-companion'),
			},

			{
				id: 'comments',
				label: __('Comments', 'blocksy-companion'),
			},

			{
				id: 'terms',
				label: __('Terms', 'blocksy-companion'),
			},

			{
				id: 'author',
				label: __('Author', 'blocksy-companion'),
			},

			{
				id: 'featured_image',
				label: __('Featured Image', 'blocksy-companion'),
			},

			{
				id: 'author_avatar',
				label: __('Author Avatar', 'blocksy-companion'),
			},
		]
	}

	if (!termId && isContentBlock) {
		fields = [
			...fields,

			{
				id: 'archive_title',
				label: __('Archive Title', 'blocksy-companion'),
			},

			{
				id: 'archive_description',
				label: __('Archive Description', 'blocksy-companion'),
			},

			{
				id: 'archive_image',
				label: __('Archive Image', 'blocksy-companion'),
			},
		]
	}

	return {
		provider: 'wp',
		fields,
	}
}

const wooFields = (postType, taxonomies = []) => {
	const hasWoo = typeof window.wc !== 'undefined'

	if (!hasWoo || postType !== 'product') {
		return null
	}

	const hasBrands = (taxonomies || []).find(
		({ slug }) => slug === 'product_brands'
	)

	return {
		provider: 'woo',
		fields: [
			{
				id: 'price',
				label: __('Price', 'blocksy-companion'),
			},
			{
				id: 'rating',
				label: __('Rating', 'blocksy-companion'),
			},
			{
				id: 'stock_status',
				label: __('Stock Status', 'blocksy-companion'),
			},
			{
				id: 'sku',
				label: __('SKU', 'blocksy-companion'),
			},
			...(hasBrands
				? [
						{
							id: 'brands',
							label: __('Brands', 'blocksy-companion'),
						},
				  ]
				: []),
		],
	}
}

const useDynamicDataDescriptor = ({ postId, postType, termId, taxonomy }) => {
	const taxonomies = useTaxonomies(postType)

	const [additionalFields, setAdditionalFields] = useState([])

	useEffect(() => {
		if (postId && !termId) {
			cachedFetch(
				`${wp.ajax.settings.url}?action=blocksy_blocks_retrieve_dynamic_data_descriptor`,
				{
					post_id: postId,
				}
			)
				.then((response) => response.json())
				.then(({ success, data }) => {
					setAdditionalFields(data.fields)
				})
		}
	}, [postId, termId])

	const fieldsDescriptor = {
		fields: [wpFields({ termId })],
	}

	const maybeWooFields = wooFields(postType, taxonomies)

	if (maybeWooFields) {
		fieldsDescriptor.fields.push(maybeWooFields)
	}

	if (additionalFields.length > 0) {
		fieldsDescriptor.fields = [
			...fieldsDescriptor.fields,
			...additionalFields,
		]
	}

	return {
		fieldsDescriptor,
		options,
		fieldsChoices: fieldsDescriptor.fields.reduce(
			(acc, currentProvider) => [
				...acc,
				...currentProvider.fields
					.filter((field) => {
						if (
							currentProvider.provider !== 'wp' ||
							field.id !== 'terms'
						) {
							return true
						}

						return taxonomies && taxonomies.length > 0
					})
					.map((field) => ({
						group: getLabelForProvider(currentProvider.provider),
						key: `${currentProvider.provider}:${field.id}`,
						value: field.label,
					})),
			],
			[]
		),
	}
}

export default useDynamicDataDescriptor
