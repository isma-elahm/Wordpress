import { useEffect, useMemo, createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'

import Preview from './Preview'

import AlignmentControls from './components/AlignmentControls'

import useDynamicDataDescriptor from './hooks/use-dynamic-data-descriptor'

import DynamicDataInspectorControls from './components/InspectorControls'

import { useTaxonomies } from '../query/edit/utils/utils'

const Edit = ({
	clientId,

	attributes,
	setAttributes,

	context,
}) => {
	const { postType, taxonomy } = context

	const { fieldsDescriptor, options, fieldsChoices } =
		useDynamicDataDescriptor(context)

	const taxonomies = useTaxonomies(postType)

	const fieldDescriptor = useMemo(() => {
		if (!attributes.field || !fieldsDescriptor) {
			return null
		}

		const [provider, field] = attributes.field.split(':')

		const providerFields = fieldsDescriptor.fields.find(
			({ provider: p }) => p === provider
		)

		if (!providerFields) {
			return null
		}

		const maybeFieldDescriptor = providerFields.fields.find(
			({ id }) => id === field
		)

		if (!maybeFieldDescriptor) {
			return null
		}

		return {
			...maybeFieldDescriptor,
			provider: providerFields.provider,
		}
	}, [attributes.field, fieldsDescriptor])

	useEffect(() => {
		if (attributes.field === 'wp:title' && taxonomy) {
			setAttributes({
				field: 'wp:term_title',
			})
		}
	}, [taxonomy, attributes.fiel])

	if (!fieldDescriptor) {
		return null
	}

	return (
		<>
			<AlignmentControls
				fieldDescriptor={fieldDescriptor}
				attributes={attributes}
				setAttributes={setAttributes}
			/>

			<Preview
				attributes={attributes}
				fieldsDescriptor={fieldsDescriptor}
				fieldDescriptor={fieldDescriptor}
				{...context}
			/>

			<DynamicDataInspectorControls
				options={options}
				fieldDescriptor={fieldDescriptor}
				attributes={attributes}
				setAttributes={setAttributes}
				fieldsChoices={fieldsChoices}
				clientId={clientId}
				fieldsDescriptor={fieldsDescriptor}
				taxonomies={taxonomies}
			/>
		</>
	)
}

export default Edit
