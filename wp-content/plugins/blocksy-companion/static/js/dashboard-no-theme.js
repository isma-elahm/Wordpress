import { createElement, render, useState } from '@wordpress/element'
import * as check from '@wordpress/element'
import { __ } from 'ct-i18n'

import NoTheme from './dashboard/NoTheme'
import VersionMismatchNotice from './notifications/VersionMismatchNotice'

const Dashboard = () => {
	if (ctDashboardLocalizations.theme_version_mismatch) {
		return (
			<VersionMismatchNotice
				mismatched_version_descriptor={
					ctDashboardLocalizations.theme_version_mismatch
				}
			/>
		)
	}

	return <NoTheme />
}

document.addEventListener('DOMContentLoaded', () => {
	if (document.getElementById('ct-dashboard')) {
		render(<Dashboard />, document.getElementById('ct-dashboard'))
	}
})
