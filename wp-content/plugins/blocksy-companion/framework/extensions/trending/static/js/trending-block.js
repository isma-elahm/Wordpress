const store = {}

const cachedFetch = (url) =>
	store[url]
		? new Promise((resolve) => {
				resolve(store[url])
				if (!window.ct_customizer_localizations) {
					store[url] = store[url].clone()
				}
		  })
		: new Promise((resolve) =>
				fetch(url).then((response) => {
					resolve(response)

					if (!window.ct_customizer_localizations) {
						store[url] = response.clone()
					}
				})
		  )

export const loadPage = (args = {}) => {
	args = {
		el: null,
		// prev | next
		action: null,

		...args,
	}

	if (!args.el) {
		return
	}

	if (!args.action) {
		return
	}

	if (args.el.classList.contains('ct-loading')) {
		return
	}

	let currentPage = parseInt(args.el.dataset.page, 10)

	if (args.action === 'prev' && currentPage === 1) {
		return
	}

	if (args.el.querySelectorAll('.ct-trending-block-item').length < 4) {
		if (currentPage === 1) {
			return
		}
	}

	if (args.el.dataset.page.indexOf('last') > -1) {
		if (args.action === 'next') {
			return
		}
	}

	args.el.querySelectorAll('.ct-trending-block-item').forEach((el, idx) => {
		el.style.opacity = 0
		el.style.transform = 'translateY(3px)'
		el.style.transitionDelay = `${idx ? idx * 0.15 - 0.05 : 0}s`
	})

	let newPage = args.action === 'prev' ? currentPage - 1 : currentPage + 1

	Promise.all([
		new Promise((resolve) => {
			requestAnimationFrame(() => {
				setTimeout(() => resolve(), 650)
			})
		}),

		cachedFetch(
			`${ct_localizations.ajax_url}?action=blocksy_get_trending_posts&page=${newPage}`
		).then((response) => response.json()),
	]).then(([_, { success, data }]) => {
		if (!success) {
			return
		}

		let {
			posts: { is_last_page, posts },
		} = data

		args.el.dataset.page = `${newPage}${is_last_page ? ':last' : ''}`
		;[...args.el.querySelectorAll('.ct-trending-block-item')].map((el) =>
			el.remove()
		)

		posts.map((post, idx) =>
			args.el.insertAdjacentHTML(
				'beforeend',

				`<div class="ct-trending-block-item" style="opacity: 0; transform: translateY(3px); transition-delay: ${
					idx ? idx * 0.15 - 0.05 : 0
				}s;">
					${post.image}

					<div class="ct-trending-block-item-content">
						${post.taxonomy}
						<a href="${post.url}" class="ct-post-title">
							${post.title}
						</a>
						${post.price}
					</div>
				</a>`
			)
		)

		setTimeout(() => {
			args.el
				.querySelectorAll('.ct-trending-block-item')
				.forEach((el, idx) => {
					el.style.opacity = 1
					el.style.transform = 'translateY(0)'
					el.style.transitionDelay = `${idx ? idx * 0.15 - 0.05 : 0}s`
				})

			requestAnimationFrame(() => {
				setTimeout(() => {
					args.el
						.querySelectorAll('.ct-trending-block-item')
						.forEach((el) => {
							el.removeAttribute('style')
						})
				}, 650)
			})
		}, 50)
	})
}
