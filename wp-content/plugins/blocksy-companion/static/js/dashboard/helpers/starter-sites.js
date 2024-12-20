import cachedFetch from 'ct-wordpress-helpers/cached-fetch'

export const getStarterSitesStatus = () => {
	const url = new URL('https://startersites.io?route=v2/demo/get_all')

	Object.keys(
		ctDashboardLocalizations.plugin_data.retrieve_demos_data
	).forEach((key) => {
		url.searchParams.append(
			key,
			ctDashboardLocalizations.plugin_data.retrieve_demos_data[key]
		)
	})

	if (ctDashboardLocalizations.plugin_data.is_pro) {
		url.searchParams.append('is_pro', 'true')
	}

	url.searchParams.append(
		'companion_version',
		ctDashboardLocalizations.plugin_data.plugin_version
	)

	return cachedFetch(
		url.toString(),
		{},
		{
			method: 'GET',
			headers: {
				'Content-Type': 'application/json',
				Accept: 'application/json',
			},
		}
	)
}
