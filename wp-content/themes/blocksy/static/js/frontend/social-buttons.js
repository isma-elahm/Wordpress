import $script from 'scriptjs'

export const mount = (el, { event }) => {
	if (el.dataset.network === 'pinterest') {
		event.preventDefault()
		if (window.PinUtils) {
			window.PinUtils.pinAny()
		} else {
			$script(
				'https://assets.pinterest.com/js/pinit.js',

				() => {
					// $log.info('Pinterest script loaded.')

					setTimeout(() => {
						window.PinUtils.pinAny()
					}, 300)
				}
			)
		}

		return
	}

	if (el.dataset.network === 'clipboard') {
		event.preventDefault()

		const text =
			el.href.indexOf('wish_list_id') !== -1
				? el.href
				: window.location.href

		const tooltip = el.querySelector('.ct-tooltip')
		let initialText = ''

		if (tooltip) {
			initialText = tooltip.innerHTML
		}

		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(text)

			if (tooltip) {
				tooltip.innerHTML = ct_localizations.clipboard_copied
			}
		} else {
			const textArea = document.createElement('textarea')
			textArea.value = text

			// Move textarea out of the viewport so it's not visible
			textArea.style.position = 'absolute'
			textArea.style.left = '-999999px'

			document.body.prepend(textArea)
			textArea.select()

			try {
				document.execCommand('copy')
			} catch (error) {
				console.error(error)

				if (tooltip) {
					tooltip.innerHTML = ct_localizations.clipboard_failed
				}
			} finally {
				textArea.remove()

				if (tooltip) {
					tooltip.innerHTML = ct_localizations.clipboard_copied
				}
			}
		}

		setTimeout(() => {
			if (tooltip) {
				tooltip.innerText = initialText
			}
		}, 2000)

		return
	}

	if (el.hasClickListener) {
		return
	}

	el.hasClickListener = true
	el.addEventListener('click', (e) => {
		e.preventDefault()

		const url = el.href
		const title = ''
		const w = 600
		const h = 500

		// PopupCenter(el.querySelector('a').href, '', 600, 500)
		// Fixes dual-screen position
		// Most browsers      Firefox
		var dualScreenLeft =
			window.screenLeft != undefined ? window.screenLeft : screen.left
		var dualScreenTop =
			window.screenTop != undefined ? window.screenTop : screen.top

		var width = window.innerWidth
			? window.innerWidth
			: document.documentElement.clientWidth
			? document.documentElement.clientWidth
			: screen.width
		var height = window.innerHeight
			? window.innerHeight
			: document.documentElement.clientHeight
			? document.documentElement.clientHeight
			: screen.height

		var left = width / 2 - w / 2 + dualScreenLeft
		var top = height / 2 - h / 2 + dualScreenTop

		var newWindow = window.open(
			url,
			title,
			'scrollbars=yes, width=' +
				w +
				', height=' +
				h +
				', top=' +
				top +
				', left=' +
				left
		)

		// Puts focus on the newWindow
		if (window.focus) {
			newWindow.focus()
		}
	})
}
