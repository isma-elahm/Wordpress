import { createElement } from '@wordpress/element'
import { useSelect } from '@wordpress/data'
import { __ } from 'ct-i18n'

import classnames from 'classnames'

const termLabels = {
	'wp:term_title': __('Term Title', 'blocksy-companion'),
	'wp:term_description': __('Term Description', 'blocksy-companion'),
	'wp:term_count': __('Term Count', 'blocksy-companion'),
}

const fieldKeys = {
	'wp:term_title': 'name',
	'wp:term_description': 'description',
	'wp:term_count': 'count',
}

const TermTextPreview = ({
	termId,
	taxonomy,

	attributes,
	attributes: { has_field_link },

	fieldsDescriptor,
}) => {
	const { terms } = useSelect((select) => {
		return {
			terms:
				select('core').getEntityRecords('taxonomy', taxonomy, {
					per_page: 1,
					include: [termId],
				}) || [],
		}
	})

	if (!terms.length) {
		return termLabels[attributes.field] || termLabels['wp:term_title']
	}

	let TagName = 'span'

	let attrs = {}

	if (has_field_link === 'yes') {
		TagName = 'a'

		attrs.href = '#'
		attrs.rel = 'noopener noreferrer'
	}

	const valueToRender = terms[0][fieldKeys[attributes.field]]

	return (
		<TagName
			{...attrs}
			className={classnames(
				{
					[`ct-term-${terms[0].id}`]:
						attributes.termAccentColor === 'yes',
				},
				attributes.termClass
			)}
			dangerouslySetInnerHTML={{ __html: valueToRender }}
		/>
	)
}

export default TermTextPreview
