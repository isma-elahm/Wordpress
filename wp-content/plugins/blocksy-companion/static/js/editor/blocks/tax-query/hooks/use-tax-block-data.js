import { useEffect, useState } from '@wordpress/element'

import cachedFetch from 'ct-wordpress-helpers/cached-fetch'

export const useTaxBlockData = ({ attributes }) => {
	const [blockData, setBlockData] = useState(null)

	useEffect(() => {
		const queryString = new URLSearchParams(window.location.search)

		cachedFetch(
			`${wp.ajax.settings.url}?action=blocksy_get_tax_block_data${
				queryString.get('lang')
					? '&lang=' + queryString.get('lang')
					: ''
			}`,

			{
				attributes,
			},

			{
				// Abort intermediary requests.
				fetcherName: `tax-block-data-${attributes.uniqueId}`,

				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},

				method: 'POST',
			}
		)
			.then((response) => response.json())
			.then(({ success, data }) => {
				if (!success) {
					return
				}

				setBlockData(data)
			})
			.catch((e) => {})
	}, [attributes])

	return {
		blockData,
	}
}
