export const getRequestUrl = (args = {}) => {
	args = {
		formEl: null,
		perPage: 5,
		inputValue: '',

		...args,
	}

	if (!args.formEl) {
		return ''
	}

	let postTypes = ['any']

	const maybeSingularPostType =
		args.formEl.querySelector('[name="post_type"]')

	const maybeCtPostType = args.formEl.querySelector('[name="ct_post_type"]')

	if (maybeCtPostType) {
		postTypes = maybeCtPostType.value.split(':')
	} else {
		if (maybeSingularPostType) {
			postTypes = [maybeSingularPostType.value]
		}
	}

	const searchTaxonomies =
		args.formEl.querySelector('[name="ct_search_taxonomies"]')?.value || ''

	const productPrice = !!args.formEl.closest(
		'[data-live-results*="product_price"]'
	)

	const productStatus = !!args.formEl.closest(
		'[data-live-results*="product_status"]'
	)

	const queryCategory = args.formEl.querySelector('[name="ct_tax_query"]')
		? args.formEl.querySelector('[name="ct_tax_query"]').value
		: ''

	const params = new URLSearchParams()

	params.append('ct_live_search', 'true')

	// Doesn't look like we need any other extra info
	// params.append('_embed', '1')

	params.append('type', 'post')

	if (postTypes.length === 1) {
		params.append('subtype', postTypes[0])
	} else {
		postTypes.forEach((postType) => {
			params.append('subtype[]', postType)
		})
	}

	params.append('per_page', args.perPage)

	if (productPrice === 'true' || productPrice === true) {
		params.append('product_price', productPrice)
	}

	if (productStatus === 'true' || productStatus === true) {
		params.append('product_status', productStatus)
	}

	if (queryCategory) {
		params.append('ct_tax_query', queryCategory)
	}

	if (searchTaxonomies) {
		params.append('ct_search_taxonomies', searchTaxonomies)
	}

	if (ct_localizations.lang) {
		params.append('lang', ct_localizations.lang)
	}

	params.append('search', args.inputValue)

	return `${ct_localizations.rest_url}wp/v2/search${
		ct_localizations.rest_url.indexOf('?') > -1 ? '&' : '?'
	}${params.toString()}`
}
