import ctEvents from 'ct-events'
import cookie from 'js-cookie'

import { loadStyle } from 'blocksy-frontend'

const onKeydown = (event) => {
	if (event.keyCode !== 27) return
	hideCookieConsent(document.querySelector('.cookie-notification'))
}

const showCookieConsent = (node) => {
	document.addEventListener('keyup', onKeydown)

	requestAnimationFrame(() => {
		node.classList.remove('ct-fade-in-start')
		node.classList.add('ct-fade-in-end')

		whenTransitionEnds(node, () => {
			node.classList.remove('ct-fade-in-end')
		})
	})
}

const hideCookieConsent = (node) => {
	document.removeEventListener('keyup', onKeydown)

	node.classList.add('ct-fade-start')

	requestAnimationFrame(() => {
		node.classList.remove('ct-fade-start')
		node.classList.add('ct-fade-end')

		whenTransitionEnds(node, () => {
			node.parentNode.removeChild(node)
		})
	})
}

export const onDocumentLoaded = (cb) => {
	if (/comp|inter|loaded/.test(document.readyState)) {
		cb()
	} else {
		document.addEventListener('DOMContentLoaded', cb, false)
	}
}

let cookiesData = {}

const mountCookieNotification = () => {
	const notification = document.querySelector('.cookie-notification')

	showCookieConsent(notification)
	;[...notification.querySelectorAll('button')].map((el) => {
		el.addEventListener('click', (e) => {
			e.preventDefault()

			if (el.classList.contains('ct-cookies-accept-button')) {
				const periods = {
					onehour: 36e5,
					oneday: 864e5,
					oneweek: 7 * 864e5,
					onemonth: 31 * 864e5,
					threemonths: 3 * 31 * 864e5,
					sixmonths: 6 * 31 * 864e5,
					oneyear: 365 * 864e5,
					forever: 10000 * 864e5,
				}

				cookie.set('blocksy_cookies_consent_accepted', 'true', {
					expires: new Date(
						new Date() * 1 +
							periods[el.closest('[data-period]').dataset.period]
					),
					sameSite: 'lax',
				})

				if (cookiesData && cookiesData.scripts) {
					cookiesData.scripts.map((scriptTag) => {
						const parser = new DOMParser()
						const html = parser.parseFromString(
							scriptTag,
							'text/html'
						)

						const scriptsToLoad = html.querySelectorAll('script')

						if (!scriptsToLoad) return
						;[...scriptsToLoad].map((script) => {
							const newTag = document.createElement('script')

							if (script.innerHTML) {
								newTag.innerHTML = script.innerHTML
							}

							;[...script.attributes].map(({ name, value }) => {
								newTag.setAttribute(name, value)
							})

							document.head.appendChild(newTag)
						})
					})
				}
			}

			if (el.classList.contains('ct-cookies-decline-button')) {
				const periods = {
					onehour: 36e5,
					oneday: 864e5,
					oneweek: 7 * 864e5,
					onemonth: 31 * 864e5,
					threemonths: 3 * 31 * 864e5,
					sixmonths: 6 * 31 * 864e5,
					oneyear: 365 * 864e5,
					forever: 10000 * 864e5,
				}

				cookie.set('blocksy_cookies_consent_accepted', 'no', {
					expires: new Date(
						new Date() * 1 +
							periods[el.closest('[data-period]').dataset.period]
					),
					sameSite: 'lax',
				})
			}

			hideCookieConsent(notification)
		})
	})
}

const initCookies = () => {
	if (cookie.get('blocksy_cookies_consent_accepted')) {
		return
	}

	const body = new FormData()
	body.append('action', 'blc_load_cookies_consent_data')

	fetch(ct_localizations.ajax_url, {
		method: 'POST',
		body,
	})
		.then((r) => r.json())
		.then(({ data }) => {
			cookiesData = data

			const drawerCanvas = document.querySelector(
				'.ct-drawer-canvas[data-location="start"]'
			)

			loadStyle(ct_localizations.dynamic_styles.cookie_notification).then(
				() => {
					drawerCanvas.insertAdjacentHTML(
						'beforeend',
						cookiesData.consent_output
					)

					mountCookieNotification()
				}
			)
		})
}

onDocumentLoaded(() => {
	initCookies()

	if (ctEvents) {
		ctEvents.on('blocksy:cookies:init', () => {
			initCookies()
		})
	}
})

function whenTransitionEnds(el, cb) {
	setTimeout(() => {
		cb()
	}, 300)
	return

	const end = () => {
		el.removeEventListener('transitionend', onEnd)
		cb()
	}

	const onEnd = (e) => {
		if (e.target === el) {
			end()
		}
	}

	el.addEventListener('transitionend', onEnd)
}
