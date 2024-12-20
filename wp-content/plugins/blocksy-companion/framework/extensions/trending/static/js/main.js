import { loadPage } from './trending-block'
import { registerDynamicChunk } from 'blocksy-frontend'

registerDynamicChunk('blocksy_ext_trending', {
	mount: (el, { event }) => {
		const loadingEl = el.closest('[data-page]')

		if (el.classList.contains('ct-arrow-prev')) {
			loadPage({ el: loadingEl, action: 'prev' })
		}

		if (el.classList.contains('ct-arrow-next')) {
			loadPage({ el: loadingEl, action: 'next' })
		}
	},
})
