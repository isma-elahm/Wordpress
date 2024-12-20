export const mount = () => {
	if (!window.turnstile) {
		return
	}

	const forms = document.querySelectorAll('.ct-popup .cf-turnstile')

	if (!forms.length) {
		return
	}

	forms.forEach((form) => {
		turnstile.remove(form)
		turnstile.render(form)
		turnstile.reset(form)
	})
}
