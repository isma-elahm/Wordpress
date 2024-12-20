import ctEvents from 'ct-events'
import $ from 'jquery'

let deepLinkLocation = null

export const getDeepLinkPanel = () =>
	deepLinkLocation ? deepLinkLocation.split(':')[1] : false
export const removeDeepLink = () => (deepLinkLocation = null)

if (wp.customize) {
	wp.customize.bind('ready', () => {
		wp.customize.previewer.bind('ct-initiate-deep-link', (location) => {
			const [section, panel] = location.split(':')
			const expanded = Object.values(wp.customize.section._value).find(
				(e) => e.expanded()
			)

			if (!expanded || expanded.id !== section) {
				deepLinkLocation = location
				wp.customize.section(section).expand()

				return
			}

			ctEvents.trigger('ct-deep-link-start', location)
		})

		// wp.customize.preview.send('ct-trigger-autosave')
		wp.customize.previewer.bind('ct-trigger-autosave', () => {
			// https://github.com/WordPress/WordPress/blob/38fdd7bb3afcd59d51bc7bafcaa3d78820e3593b/wp-admin/js/customize-controls.js#L9336
			//
			// Trigger customizer autosave
			wp.customize
				.requestChangesetUpdate({}, { autosave: true })
				.done(() => {
					wp.customize.previewer.send('ct-trigger-autosave-done')
				})
		})
	})
}
