import { useMemo, useState, useEffect } from '@wordpress/element'

import { getStableJsonKey } from 'ct-wordpress-helpers/get-stable-json-key'
import cachedFetch from 'ct-wordpress-helpers/cached-fetch'

// TODO: maybe rename this hook to show that it can be used for something else
// other than custom fields.
//
// Potentially, termId can also be provided to get term data.
const useCustomFieldData = ({ postId, fieldDescriptor }) => {
	const [fieldData, setFieldData] = useState({})

	const requestDescriptor = useMemo(() => {
		const url = `${wp.ajax.settings.url}?action=blocksy_dynamic_data_block_custom_field_data`

		const body = {
			post_id: postId,
			field_provider: fieldDescriptor.provider,
			field_id: fieldDescriptor.id,
		}

		return {
			url,
			body,
			cacheKey: getStableJsonKey({ ...body, url }),
		}
	}, [postId, fieldDescriptor.provider, fieldDescriptor.id])

	useEffect(() => {
		if (!fieldData[requestDescriptor.cacheKey]) {
			cachedFetch(requestDescriptor.url, requestDescriptor.body)
				.then((response) => response.json())
				.then(({ success, data }) => {
					if (!success) {
						return
					}

					setFieldData((prev) => ({
						...prev,
						[requestDescriptor.cacheKey]: data.field_data,
					}))
				})
		}
	}, [requestDescriptor, fieldData])

	return {
		fieldData: fieldData[requestDescriptor.cacheKey]
			? fieldData[requestDescriptor.cacheKey]
			: null,
	}
}

export default useCustomFieldData
