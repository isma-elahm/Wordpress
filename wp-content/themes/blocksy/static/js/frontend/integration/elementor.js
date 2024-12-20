import ctEvents from 'ct-events'

export const mount = () => {
	if (
		!window.elementorFrontend ||
		!window.elementorFrontend.utils ||
		!window.elementorFrontend.utils.anchors
	) {
		return
	}

	setTimeout(() => {
		elementorFrontend.elements.$document.off(
			'click',
			elementorFrontend.utils.anchors.getSettings('selectors.links'),
			elementorFrontend.utils.anchors.handleAnchorLinks
		)
	}, 1000)
}
