import { registerDynamicChunk } from 'blocksy-frontend'

const submitAjax = (form) => {
	const body = new FormData(form)

	body.append('action', 'blc_newsletter_subscribe_process_ajax_subscribe')

	body.append('GROUP', form.dataset.provider.split(':')[1])

	form.classList.remove('subscribe-error', 'subscribe-success')
	form.classList.add('subscribe-loading')

	fetch(ct_localizations.ajax_url, {
		method: 'POST',
		body,
	})
		.then((r) => r.json())
		.then(({ success, data }) => {
			form.classList.remove('subscribe-loading')

			form.classList.add(
				data.result === 'no' ? 'subscribe-error' : 'subscribe-success'
			)

			form.querySelector('.ct-newsletter-subscribe-message').innerHTML =
				data.message
		})
}

registerDynamicChunk('blocksy_ext_newsletter_subscribe', {
	mount: (el, { event }) => {
		const form = event.target

		submitAjax(form)
	},
})
