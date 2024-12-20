import $ from 'jquery'
import { Flexy } from 'flexy'
import ctEvents from 'ct-events'
import { getCurrentScreen } from '../frontend/helpers/current-screen'

import { pauseVideo, maybePlayAutoplayedVideo } from './helpers/video'

import { getScalarOrCallback } from './helpers/get-scalar-or-callback'

export const mount = (sliderEl, args) => {
	// sliderEl = sliderEl.parentNode

	args = {
		event: null,
		flexyOptions: {},
		...args,
	}

	let originalSliderEl = sliderEl

	sliderEl = getScalarOrCallback(sliderEl)

	if (sliderEl.flexy) {
		return sliderEl.flexy
	}

	let maybePillsSlider = sliderEl.querySelector('.flexy-pills [data-flexy]')

	let leftArrow = sliderEl.querySelector('.flexy .flexy-arrow-prev')
	let rightArrow = sliderEl.querySelector('.flexy .flexy-arrow-next')

	const maybeSuggested = sliderEl.closest('.ct-suggested-products')

	if (maybeSuggested) {
		leftArrow = maybeSuggested.querySelector('.ct-arrow-prev')
		rightArrow = maybeSuggested.querySelector('.ct-arrow-next')
	}

	const isPillsDragEvent =
		args.event && args.event.target.closest('.flexy-pills > * > *')

	const inst = new Flexy(
		() => {
			const sliderEl = getScalarOrCallback(originalSliderEl)

			if (!sliderEl) {
				return null
			}

			return sliderEl.querySelector('.flexy-items')
		},

		{
			flexyAttributeEl: originalSliderEl,
			elementsThatDoNotStartDrag: ['.twentytwenty-handle'],

			...(args.event && !isPillsDragEvent
				? { initialDragEvent: args.event }
				: {}),

			autoplay:
				Object.keys(sliderEl.dataset).indexOf('autoplay') > -1 &&
				parseInt(sliderEl.dataset.autoplay, 10)
					? sliderEl.dataset.autoplay
					: false,

			...(sliderEl.querySelector('.flexy-pills')
				? {
						pillsContainerSelector:
							sliderEl.querySelector('.flexy-pills')
								.firstElementChild,
				  }
				: {}),
			leftArrow,
			rightArrow,
			scaleRotateEffect: false,

			onDragStart: (e) => {
				if (!e.target.closest('.flexy-items')) {
					return
				}

				Array.from(
					e.target
						.closest('.flexy-items')
						.querySelectorAll('.zoomImg')
				).map((img) => {
					$(img).stop().fadeTo(120, 0)
				})
			},

			// viewport | container
			wrapAroundMode:
				sliderEl.dataset.wrap === 'viewport' ? 'viewport' : 'container',

			...(maybePillsSlider
				? {
						pillsFlexyInstance: maybePillsSlider,
				  }
				: {}),

			onSlideChange: (instance, payload) => {
				ctEvents.trigger('blocksy:frontend:flexy:slide-change', {
					instance,
					payload,
				})
			},

			...(args.flexyOptions || {}),
		}
	)

	if (maybePillsSlider) {
		const inst = new Flexy(maybePillsSlider, {
			elementsThatDoNotStartDrag: ['.twentytwenty-handle'],
			// viewport | container
			wrapAroundMode:
				maybePillsSlider.dataset.wrap === 'viewport'
					? 'viewport'
					: 'container',

			...(args.event && isPillsDragEvent
				? { initialDragEvent: args.event }
				: {}),

			leftArrow:
				maybePillsSlider.parentNode.querySelector('.flexy-arrow-prev'),
			rightArrow:
				maybePillsSlider.parentNode.querySelector('.flexy-arrow-next'),

			...(maybePillsSlider.closest('.thumbs-left') &&
			getCurrentScreen({ withTablet: true }) !== 'mobile'
				? {
						orientation: 'vertical',
				  }
				: {}),
		})

		maybePillsSlider.flexy = inst
	}

	sliderEl.flexy = inst

	return inst
}

export { Flexy } from 'flexy'
