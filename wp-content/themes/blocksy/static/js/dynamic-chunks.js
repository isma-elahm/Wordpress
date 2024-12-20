import $script from 'scriptjs'

import { handleTrigger } from './frontend/dynamic-chunks/triggers'

let loadedChunks = {}
let intersectionObserver = null

export const loadDynamicChunk = (chunkId) => {
	return new Promise((resolve, reject) => {
		let chunk = ct_localizations.dynamic_js_chunks.find(
			(chunk) => chunk.id === chunkId
		)

		if (!chunk) {
			reject()
		}

		if (loadedChunks[chunk.id]) {
			resolve({ chunk: loadedChunks[chunk.id], isInitial: false })
		} else {
			loadedChunks[chunk.id] = {
				state: 'loading',
			}

			if (chunk.global_data) {
				chunk.global_data.map((data) => {
					if (!data.var || !data.data) {
						return
					}

					window[data.var] = data.data
				})
			}

			if (chunk.raw_html) {
				if (!document.querySelector(chunk.raw_html.selector)) {
					document.body.insertAdjacentHTML(
						'beforeend',
						chunk.raw_html.html
					)
				}
			}

			if (chunk.deps) {
				const depsThatAreNotLoadedIds = chunk.deps.filter(
					(id) =>
						!document.querySelector(
							`script[src*="${chunk.deps_data[id]}"]`
						)
				)
				const depsThatAreNotLoaded = depsThatAreNotLoadedIds.map(
					(id) => chunk.deps_data[id]
				)

				;[...depsThatAreNotLoadedIds, 'root']
					.map((x) => () => {
						return new Promise((depsResolve) => {
							if (x === 'root') {
								$script([chunk.url], () => {
									depsResolve()
									resolve({
										chunk: loadedChunks[chunk.id],
										isInitial: true,
									})
								})
								return
							}

							$script([chunk.deps_data[x]], () => {
								depsResolve()
							})
						})
					})
					.reduce(
						(before, after) => before.then((_) => after()),
						Promise.resolve()
					)
			} else {
				$script(chunk.url, () => {
					resolve({ chunk: loadedChunks[chunk.id], isInitial: true })
				})
			}
		}
	})
}

const loadChunkWithPayload = (chunk, payload = {}, el = null) => {
	const immediateMount = () => {
		if (!loadedChunks[chunk.id].mount) {
			return
		}

		if (el) {
			loadedChunks[chunk.id].mount(el, payload)
		} else {
			;[...document.querySelectorAll(chunk.selector)].map((el) => {
				loadedChunks[chunk.id].mount(el, payload)
			})
		}
	}

	loadDynamicChunk(chunk.id)
		.then(({ isInitial }) => {
			if (isInitial) {
				immediateMount()
			}

			// This is special case when re-triggered & payload present.
			if (!isInitial && payload) {
				immediateMount()
			}
		})
		.catch((e) => {
			console.error('Cannot load chunk', chunk.id, e)
		})
}

const addChunkToIntersectionObserver = (chunk) => {
	if (!window.IntersectionObserver) {
		return
	}

	if (!intersectionObserver) {
		intersectionObserver = new IntersectionObserver((entries) => {
			entries.map(({ boundingClientRect, target, isIntersecting }) => {
				const chunk = target.__chunk__

				if (!isIntersecting && boundingClientRect.y > 0) {
					return
				}

				let state = `target-before-bottom`

				if (!isIntersecting && boundingClientRect.y < 0) {
					state = 'target-after-bottom'
				}

				if (
					state === 'target-before-bottom' &&
					!loadedChunks[chunk.id]
				) {
					return
				}

				loadChunkWithPayload(chunk, { state, target }, chunk.el)
			})
		})
	}

	;[...document.querySelectorAll(chunk.selector)].map((el) => {
		if (el.ioObserving) {
			return
		}

		el.ioObserving = true

		const target = document.querySelector(chunk.target)

		if (!target) {
			return
		}

		target.__chunk__ = { ...chunk, el }

		intersectionObserver.observe(target)
	})
}

export const mountDynamicChunks = () => {
	ct_localizations.dynamic_js_chunks.map((chunk) => {
		if (!chunk.id) {
			return
		}

		// This is a potential problem for cases when we have multiple triggers
		if (!document.querySelector(chunk.selector)) {
			return
		}

		if (!chunk.trigger) {
			loadChunkWithPayload(chunk, null)
			return
		}

		;(Array.isArray(chunk.trigger) ? chunk.trigger : [chunk.trigger]).map(
			(trigger) => {
				trigger = trigger.trigger
					? trigger
					: {
							trigger,
							selector: chunk.selector,
					  }

				if (trigger.trigger === 'intersection-observer') {
					addChunkToIntersectionObserver(chunk)
					return
				}

				handleTrigger(
					trigger,
					chunk,
					loadChunkWithPayload,
					loadedChunks
				)
			}
		)
	})
}

export const registerDynamicChunk = (id, implementation) => {
	if (loadedChunks[id] && loadedChunks[id].state !== 'loading') {
		return
	}

	loadedChunks[id] = implementation
}
