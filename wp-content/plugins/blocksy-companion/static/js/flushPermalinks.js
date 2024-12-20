export const flushPermalinks = async () => {
	const response = await fetch(
		`${wp.ajax.settings.url}?action=blocksy_flush_permalinks&nonce=${ctDashboardLocalizations.dashboard_actions_nonce}`,

		{
			method: 'POST',
		}
	)

	if (response.status !== 200) {
		return
	}

	const { success } = await response.json()

	if (!success) {
		return
	}
}
