import $ from 'jquery'

function singleProductAddToCart(wrapper, event) {
	if (!$) return

	var form = wrapper.closest('form')

	var button = form.find(event.submitter)

	var formUrl = $(form)[0].action

	if (typeof formUrl !== 'string') {
		form.submit()

		return
	}

	var formMethod = form.attr('method')

	if (typeof formMethod === 'undefined' || formMethod == '') {
		formMethod = 'POST'
	}

	var formData = new FormData(form[0])
	formData.append(button.attr('name'), button.val())

	const quantity = [...formData.entries()].reduce(
		(total, current) =>
			total +
			(current[0].indexOf('quantity') > -1
				? parseInt(current[1], 10)
				: 0),
		0
	)

	if (quantity === 0) {
		// return
	}

	if (form.closest('.quick-view-modal').length) {
		form.closest('.quick-view-modal')
			.find('.ct-quick-add')
			.removeClass('added')

		form.closest('.quick-view-modal')
			.find('.ct-quick-add')
			.addClass('loading')
	}

	button.removeClass('added')
	button.addClass('loading')

	// Trigger event.

	$(document.body).trigger('adding_to_cart', [
		button,
		[...formData.entries()].reduce((acc, [key, value]) => {
			acc[key] = value
			return acc
		}, {}),
	])

	const url = new URL(formUrl)
	const searchParams = new URLSearchParams(url.search)
	searchParams.append('blocksy_add_to_cart', 'yes')

	if (window.ct_customizer_localizations) {
		searchParams.set('wp_customize', 'on')
	}

	url.search = searchParams.toString()

	formUrl = url.toString()

	fetch(formUrl, {
		method: formMethod,
		body: formData,

		/*
		cache: false,
		contentType: false,
		processData: false,
        */
	})
		.then((r) => r.json())
		.then(({ success, data }) => {
			if (!success) {
				return
			}

			const { notices, fragments, cart_hash } = data

			const errorSelector =
				'.woocommerce-error, .wc-block-components-notice-banner.is-error'

			const div = document.createElement('div')

			div.innerHTML = notices

			let error = div.querySelector(errorSelector)

			if (error && error.innerHTML.length > 0) {
				let notices = document.querySelector(
					'.woocommerce-notices-wrapper'
				)

				if (notices.querySelector(errorSelector)) {
					notices.querySelector(errorSelector).remove()
				}

				if (notices) {
					notices.appendChild(error)
				}

				return
			}

			$(document.body).trigger('added_to_cart', [
				fragments,
				cart_hash,
				button,
			])

			if (form.closest('.quick-view-modal').length) {
				form.closest('.quick-view-modal')
					.find('.ct-quick-add')
					.addClass('added')

				form.closest('.quick-view-modal')
					.find('.ct-quick-add')
					.removeClass('loading')
			}
		})
		.catch(() => button.removeClass('loading'))
		.finally(() => button.removeClass('loading'))
}

export const mount = (el, { event }) => {
	if (!$) {
		return
	}

	ctEvents.trigger('ct:header:update')

	singleProductAddToCart($(el), event)
}
