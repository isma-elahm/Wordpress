import { createElement } from '@wordpress/element'

import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'

import classnames from 'classnames'

import { __ } from 'ct-i18n'

import TitlePreview from './wp/TitlePreview'
import ExcerptPreview from './wp/ExcerptPreview'
import DatePreview from './wp/DatePreview'
import CommentsPreview from './wp/CommentsPreview'
import AuthorPreview from './wp/AuthorPreview'
import TaxonomyPreview from './wp/TaxonomyPreview'
import ImagePreview from './wp/ImagePreview'
import AvatarPreview from './wp/AvatarPreview'
import TermTextPreview from './wp/TermTextPreview'
import TermImagePreview from './wp/TermImagePreview'

const TextField = ({
	fieldDescriptor,
	fieldsDescriptor,

	attributes,
	attributes: { align, tagName: TagName, before, after, fallback },
	postId,
	postType,

	termId,
	taxonomy,
}) => {
	const blockProps = useBlockProps({
		className: classnames('ct-dynamic-data', {
			[`has-text-align-${align}`]: align,
		}),
	})

	const borderProps = useBorderProps(attributes)

	let Component = null

	if (fieldDescriptor.id === 'archive_title') {
		Component = () => __('Archive Title', 'blocksy-companion')
	}

	if (fieldDescriptor.id === 'archive_description') {
		Component = () => __('Archive Description', 'blocksy-companion')
	}

	if (
		fieldDescriptor.id === 'term_title' ||
		fieldDescriptor.id === 'term_description' ||
		fieldDescriptor.id === 'term_count'
	) {
		Component = TermTextPreview
	}

	if (fieldDescriptor.id === 'title') {
		Component = TitlePreview
	}

	if (fieldDescriptor.id === 'excerpt') {
		Component = ExcerptPreview
	}

	if (fieldDescriptor.id === 'date') {
		Component = DatePreview
	}

	if (fieldDescriptor.id === 'comments') {
		Component = CommentsPreview
	}

	if (fieldDescriptor.id === 'terms') {
		Component = TaxonomyPreview
	}

	if (fieldDescriptor.id === 'author') {
		Component = AuthorPreview
	}

	if (Component) {
		return (
			<TagName
				{...blockProps}
				{...borderProps}
				style={{
					...(blockProps.style || {}),
					...(borderProps.style || {}),
				}}
				className={classnames(
					blockProps.className,
					borderProps.className
				)}>
				{fieldsDescriptor && fieldsDescriptor.dynamic_styles && (
					<style>{fieldsDescriptor.dynamic_styles}</style>
				)}
				{before}

				<Component
					attributes={attributes}
					postId={postId}
					postType={postType}
					termId={termId}
					taxonomy={taxonomy}
					fallback={fallback}
					fieldsDescriptor={fieldsDescriptor}
				/>

				{after}
			</TagName>
		)
	}

	return null
}

const WpFieldPreview = (props) => {
	const { fieldDescriptor } = props

	if (fieldDescriptor.id === 'featured_image') {
		return <ImagePreview {...props} />
	}

	if (fieldDescriptor.id === 'author_avatar') {
		return <AvatarPreview {...props} />
	}

	if (fieldDescriptor.id === 'term_image') {
		return <TermImagePreview {...props} />
	}

	if (fieldDescriptor.id === 'archive_image') {
		return <ImagePreview {...props} />
	}

	return <TextField {...props} />
}

export default WpFieldPreview
