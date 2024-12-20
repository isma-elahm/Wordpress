import {
	createElement,
	useCallback,
	useState,
	useEffect,
} from '@wordpress/element'

import { __ } from 'ct-i18n'
import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'
import classnames from 'classnames'

import WpFieldPreview from './preview-parts/WpFieldPreview'
import CustomFieldPreview from './preview-parts/CustomFieldPreview'
import WooFieldPreview from './preview-parts/WooFieldPreview'

const Preview = ({
	fieldDescriptor,
	fieldsDescriptor,

	postId,
	postType,

	termId,
	taxonomy,
	termIcon,
	termImage,

	attributes,
	attributes: {
		tagName: TagName,

		align,
		field,

		before,
		after,
		fallback,
	},
}) => {
	if (fieldDescriptor.provider === 'woo') {
		return (
			<WooFieldPreview
				fieldDescriptor={fieldDescriptor}
				attributes={attributes}
				postId={postId}
				postType={postType}
			/>
		)
	}

	if (fieldDescriptor.provider === 'wp') {
		return (
			<WpFieldPreview
				fieldsDescriptor={fieldsDescriptor}
				fieldDescriptor={fieldDescriptor}
				attributes={attributes}
				postId={postId}
				postType={postType}
				termId={termId}
				taxonomy={taxonomy}
				termImage={termImage}
				termIcon={termIcon}
			/>
		)
	}

	return (
		<CustomFieldPreview
			fieldDescriptor={fieldDescriptor}
			attributes={attributes}
			postId={postId}
			postType={postType}
		/>
	)
}

export default Preview
