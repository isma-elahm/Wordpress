import { __ } from 'ct-i18n'

export const getLabelForProvider = (provider) => {
	return (
		{
			wp: 'WordPress',
			woo: 'WooCommerce',
			acf: 'ACF',
			metabox: 'MetaBox',
			custom: __('Custom', 'blocksy-companion'),
			toolset: 'Toolset',
			jetengine: 'Jet Engine',
			pods: 'Pods',
			acpt: 'ACPT',
		}[provider] || __('Unknown', 'blocksy-companion')
	)
}

export const fieldIsImageLike = (fieldDescriptor) => {
	if (fieldDescriptor.provider === 'wp') {
		return (
			fieldDescriptor.id === 'featured_image' ||
			fieldDescriptor.id === 'author_avatar' ||
			fieldDescriptor.id === 'archive_image' ||
			fieldDescriptor.id === 'term_image'
		)
	}

	return fieldDescriptor.type === 'image'
}
