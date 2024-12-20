import ctEvents from 'ct-events'
import { areWeDealingWithSafari } from '../main'
import { loadStyle } from '../helpers'

import { isIosDevice } from './helpers/is-ios-device'

// idle | loading | loaded
let stylesState = 'idle'

let prevScrollY = null

export const mount = (backTop) => {
	if (backTop.hasListener) {
		return
	}

	backTop.hasListener = true

	// Loading styles early for iOS devices
	if (isIosDevice()) {
		loadStyle(ct_localizations.dynamic_styles.back_to_top).then(() => {})
	}

	// browser window scroll (in pixels) after which the "back to top" link is shown
	// browser window scroll (in pixels) after which the "back to top" link opacity is reduced

	const compute = () => {
		var backTop = document.querySelector('.ct-back-to-top')

		if (!backTop) return

		if (window.scrollY > 300) {
			if (stylesState === 'loaded') {
				backTop.classList.add('ct-show')
			}

			if (stylesState === 'idle') {
				stylesState = 'loading'

				const cb = () => {
					backTop.removeAttribute('hidden')

					stylesState = 'loaded'
					backTop.classList.add('ct-show')
				}

				// Styles are already loaded for iOS devices
				if (isIosDevice()) {
					cb()
				} else {
					loadStyle(ct_localizations.dynamic_styles.back_to_top).then(
						() => {
							cb()
						}
					)
				}
			}
		} else {
			backTop.classList.remove('ct-show')
		}
	}

	const renderFrame = () => {
		if (prevScrollY === null || window.scrollY !== prevScrollY) {
			prevScrollY = window.scrollY
			compute()
		}

		requestAnimationFrame(renderFrame)
	}

	requestAnimationFrame(renderFrame)

	compute()

	backTop.addEventListener('click', (event) => {
		event.preventDefault()

		var start = window.scrollY
		var currentTime = null

		const animateScroll = (timestamp) => {
			if (!currentTime) currentTime = timestamp
			var progress = timestamp - currentTime

			const easeInOutQuad = (t, b, c, d) => {
				t /= d / 2
				if (t < 1) return (c / 2) * t * t + b
				t--
				return (-c / 2) * (t * (t - 2) - 1) + b
			}

			var val = Math.max(easeInOutQuad(progress, start, -start, 700), 0)

			scrollTo(0, val)

			if (progress < 700) {
				requestAnimationFrame(animateScroll)
			}
		}

		if (areWeDealingWithSafari) {
			requestAnimationFrame(animateScroll)
		} else {
			scrollTo(0, 0)
		}
	})
}
