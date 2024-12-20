import { createElement, render } from '@wordpress/element'
import VersionMismatchNotice from './notifications/VersionMismatchNotice'
import $ from 'jquery'

export const mount = (el) => {
	if (el.querySelector('.notice-blocksy-theme-version-mismatch')) {
		const container = el.querySelector(
			'.notice-blocksy-theme-version-mismatch'
		)

		render(
			<VersionMismatchNotice
				mismatched_version_descriptor={{
					productName: container.dataset.productName,
					slug: container.dataset.slug,
				}}
			/>,
			el.querySelector('.notice-blocksy-theme-version-mismatch')
		)
	}
}

document.addEventListener('DOMContentLoaded', () => {
	mount(document.body)
})
