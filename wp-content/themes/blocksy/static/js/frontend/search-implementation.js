// @jsx h
import { h } from 'dom-chef'
import classnames from 'classnames'

import { loadStyle } from '../helpers'
import { isIosDevice } from './helpers/is-ios-device'
import { whenTransitionEnds } from './helpers/when-transition-ends'

import { getRequestUrl } from './search/get-request-url'

let alreadyRunning = false

const decodeHTMLEntities = (string) => {
	var doc = new DOMParser().parseFromString(string, 'text/html')
	return doc.documentElement.textContent
}

const store = {}
let controller = null

const cachedFetch = (url, nonce = '') => {
	if (controller) {
		controller.abort('New request')
		controller = null
	}

	if (store[url]) {
		return new Promise((resolve) => {
			resolve(store[url])
			store[url] = store[url].clone()
		})
	}

	if ('AbortController' in window) {
		controller = new AbortController()
	}

	return fetch(url, {
		signal: controller.signal,
		headers: {
			'X-WP-Nonce': nonce,
		},
	}).then((response) => {
		store[url] = response.clone()

		controller = null

		return response
	})
}

const debounce = (fn, wait) => {
	var timeout

	return function () {
		if (!wait) {
			return fn.apply(this, arguments)
		}

		var context = this
		var args = arguments

		clearTimeout(timeout)

		timeout = setTimeout(function () {
			timeout = null
			return fn.apply(context, args)
		}, wait)
	}
}

const getPreviewElFor = ({
	hasThumbs,
	post: {
		// title: { rendered },
		title,
		url: href,
		_embedded = {},
		ct_featured_media,
		product_price = 0,
		product_status = '',
		placeholder_image = null,
	},
}) => {
	const decodedTitle = decodeHTMLEntities(title)

	const defaultMediaDetails = {
		sizes: {
			thumbnail: {
				source_url: placeholder_image,
			},
		},
	}

	const sizes =
		(ct_featured_media?.media_details || defaultMediaDetails).sizes || {}

	return (
		<a className="ct-search-item" role="option" key={href} {...{ href }}>
			{(ct_featured_media || placeholder_image) && hasThumbs && (
				<span
					{...{
						class: classnames({
							['ct-media-container']: true,
						}),
					}}>
					<img
						{...{
							src: sizes.thumbnail
								? sizes?.thumbnail.source_url
								: values(sizes).reduce(
										(currentSmallest, current) =>
											current.width <
											currentSmallest.width
												? current
												: currentSmallest,
										{
											width: 9999999999,
										}
								  ).source_url || ct_featured_media.source_url,
						}}
						style={{ aspectRatio: '1/1' }}
					/>
				</span>
			)}
			<span>
				{decodedTitle}

				{product_price || product_status ? (
					<span className="product-search-meta">
						{product_price ? (
							<small
								className="price"
								dangerouslySetInnerHTML={{
									__html: product_price,
								}}
								key="price"
							/>
						) : null}
						{product_status ? (
							<small
								className="stock-status"
								dangerouslySetInnerHTML={{
									__html: product_status,
								}}
								key="product-status"
							/>
						) : null}
					</span>
				) : null}
			</span>
		</a>
	)
}

export const mount = (formEl, args = {}) => {
	const clickOutsideHandler = (e) => {
		let mode = { mode: 'inline', ...args }.mode

		if (mode === 'modal') {
			return
		}

		if (formEl.contains(e.target)) {
			return
		}

		fadeOutAndRemove(formEl.querySelector('.ct-search-results'))
	}

	const maybeEl = formEl.querySelector('input[type="search"]')

	const options = {
		postType: 'any',

		// inline | modal
		mode: 'inline',

		perPage: 5,

		...args,
	}

	if (!maybeEl || !window.fetch) {
		return
	}

	let listener = debounce(async (e) => {
		document.removeEventListener('click', clickOutsideHandler)
		document.addEventListener('click', clickOutsideHandler)

		if (e.target.value.trim().length === 0) {
			fadeOutAndRemove(formEl.querySelector('.ct-search-results'))

			let maybeStatusEl = formEl.querySelector('[aria-live]')

			if (maybeStatusEl) {
				maybeStatusEl.innerHTML = ct_localizations.search_live_no_result
			}

			return
		}

		formEl.classList.add('ct-searching')

		const requestUrl = getRequestUrl({
			formEl,
			inputValue: e.target.value,
			perPage: options.perPage,
		})

		const maybeNonce = formEl.querySelector('.ct-live-results-nonce')

		try {
			const response = await cachedFetch(
				requestUrl,
				maybeNonce ? maybeNonce.value : ''
			)

			let totalAmountOfPosts = parseInt(
				response.headers.get('X-WP-Total'),
				10
			)

			await loadStyle(ct_localizations.dynamic_styles.search_lazy)

			const posts = await response.json()

			if (alreadyRunning) {
				return
			}

			alreadyRunning = true

			formEl.classList.remove('ct-searching')

			let itHadSearchResultsBefore =
				!!formEl.querySelector('.ct-search-results')

			let searchResults = formEl.querySelector('.ct-search-results')

			let { height: heightBeforeRemoval } = searchResults
				? searchResults.getBoundingClientRect()
				: 0

			if (
				searchResults &&
				!(e.target.value.trim().length === 0 || posts.length === 0)
			) {
				/**
				 * Should just quickly replace the list
				 * when results are available
				 */
				searchResults && formEl.removeChild(searchResults)
			} else {
				if (e.target.value.trim().length === 0 || posts.length === 0) {
					fadeOutAndRemove(searchResults)
				}
			}

			let searchResultsCountElLabel =
				ct_localizations.search_live_no_result

			if (posts.length > 0 && e.target.value.trim().length > 0) {
				searchResultsCountElLabel = (
					posts.length > 1
						? ct_localizations.search_live_many_results
						: ct_localizations.search_live_one_result
				).replace('%s', posts.length)
			}

			let maybeStatusEl = formEl.querySelector('[aria-live]')

			if (maybeStatusEl) {
				maybeStatusEl.innerHTML = searchResultsCountElLabel
			}

			if (posts.length > 0 && e.target.value.trim().length > 0) {
				let searchResultsEl = (
					<div
						class="ct-search-results"
						role="listbox"
						aria-label={ct_localizations.search_live_results}>
						{posts
							.filter((post) => post?.id)
							.map((post) =>
								getPreviewElFor({
									post,
									hasThumbs:
										(
											formEl.dataset.liveResults || ''
										).indexOf('thumbs') > -1,
								})
							)}

						{totalAmountOfPosts > options.perPage ? (
							<a
								className="ct-search-more"
								{...{
									href: ct_localizations.search_url.replace(
										/QUERY_STRING/,
										e.target.value
									),
								}}>
								{ct_localizations.show_more_text}
							</a>
						) : (
							[]
						)}
					</div>
				)

				formEl.appendChild(searchResultsEl)

				if (!itHadSearchResultsBefore) {
					fadeIn(formEl.querySelector('.ct-search-results'))
				} else {
					let searchResults =
						formEl.querySelector('.ct-search-results')

					let { height: heightAfterReplace } =
						searchResults.getBoundingClientRect()

					if (heightBeforeRemoval !== heightAfterReplace) {
						searchResults.style.height = `${heightBeforeRemoval}px`
						searchResults.classList.add('ct-slide')

						requestAnimationFrame(() => {
							searchResults.style.height = `${heightAfterReplace}px`

							whenTransitionEnds(searchResults, () => {
								searchResults.removeAttribute('style')

								searchResults.classList.remove('ct-slide')
							})
						})
					}
				}

				if (formEl.querySelector('.ct-search-more')) {
					formEl
						.querySelector('.ct-search-more')
						.addEventListener('click', (e) => {
							e.preventDefault()
							formEl.submit()
						})
				}

				if (isIosDevice()) {
					if (options.mode === 'modal') {
						window.scrollTo(0, 0)
					}
				}
			}

			alreadyRunning = false
		} catch (error) {
			// Ignore aborts
			if (error.message === 'New request') {
				return
			}

			console.error('Error fetching search results', error)
		}
	}, 300)

	maybeEl.addEventListener('input', listener)

	maybeEl.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') {
			e.preventDefault()
		}
	})

	maybeEl.addEventListener('focus', (e) => {
		listener(e)
	})

	if (maybeEl.value.length > 0) {
		listener({ target: maybeEl })
	}
}

function fadeOutAndRemove(el) {
	if (!el) return

	let { height } = el.getBoundingClientRect()

	el.classList.add('ct-fade-leave')
	el.style.height = `${height}px`

	el.closest('form').classList.remove('ct-has-dropdown')

	requestAnimationFrame(() => {
		el.classList.remove('ct-fade-leave')
		el.classList.add('ct-fade-leave-active')
		el.style.height = 0

		whenTransitionEnds(
			el,
			() => el.parentNode && el.parentNode.removeChild(el)
		)
	})
}

function fadeIn(el) {
	el.classList.add('ct-fade-enter')

	let { height } = el.getBoundingClientRect()

	el.classList.add('ct-fade-leave')
	el.style.height = 0

	el.closest('form').classList.add('ct-has-dropdown')

	requestAnimationFrame(() => {
		el.style.height = `${height}px`
		el.classList.remove('ct-fade-enter')
		el.classList.add('ct-fade-enter-active')

		whenTransitionEnds(el, () => el.removeAttribute('style'))
	})
}

function values(obj) {
	var result = []

	if (typeof obj == 'object' || typeof obj == 'function') {
		var keys = Object.keys(obj)
		var len = keys.length

		for (var i = 0; i < len; i++) {
			result.push(obj[keys[i]])
		}

		return result
	}
}
