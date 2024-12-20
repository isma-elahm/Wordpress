import { useRef, useEffect, memo, useState, RawHTML } from '@wordpress/element'
import { mountFlexy } from 'blocksy-options'
import { getStableJsonKey } from 'ct-wordpress-helpers/get-stable-json-key'

export const useFlexySlider = ({
	isSlideshow,
	attributes,
	context,
	toWatch,
}) => {
	const [flexyInstance, setFlexyInstance] = useState(null)
	const flexyContainerRef = useRef()

	const [sliderState, setSliderState] = useState(null)

	let flexyContainerAttr = {}
	let elementsDescriptor = []

	useEffect(() => {
		if (flexyInstance) {
			flexyInstance.scheduleSliderRecalculation()
		}
	}, [attributes, context])

	useEffect(() => {
		if (flexyInstance && !isSlideshow) {
			flexyInstance.destroy()
		}

		if (flexyInstance && isSlideshow) {
			setTimeout(() => {
				flexyInstance.refreshActivation()
			}, 50)
		}
	}, [isSlideshow, flexyInstance, toWatch])

	useEffect(() => {
		return () => {
			if (flexyInstance) {
				flexyInstance.destroy()
			}
		}
	}, [])

	if (isSlideshow) {
		flexyContainerAttr.ref = flexyContainerRef
		flexyContainerAttr.onMouseOver = () => {
			if (flexyInstance) {
				return
			}

			mountFlexy().then(({ mount: mountSlider, Flexy }) => {
				setFlexyInstance(
					mountSlider(() => flexyContainerRef.current, {
						flexyOptions: {
							dragAndDropOptions: {
								mountDragAndDropEventListener: false,
							},

							arrowsOptions: {
								mountListeners: false,
							},

							onRender: (instance, sliderState) => {
								setSliderState(sliderState)
							},
						},
					})
				)
			})
		}
	}

	if (sliderState) {
		Object.keys(sliderState.flexyAttributeElAttr).map((attrKey) => {
			flexyContainerAttr[attrKey] =
				sliderState.flexyAttributeElAttr[attrKey]
		})

		elementsDescriptor = sliderState.elementsDescriptor
	}

	return {
		flexyInstance,
		flexyContainerAttr,
		elementsDescriptor,

		elementDescriptorForIndex: (index) => {
			if (
				elementsDescriptor &&
				elementsDescriptor.length > 0 &&
				elementsDescriptor[index]
			) {
				return elementsDescriptor[index]
			}

			if (
				elementsDescriptor &&
				elementsDescriptor.length > 0 &&
				!elementsDescriptor[index]
			) {
				return elementsDescriptor[0]
			}

			return null
		},

		arrowsDescritor: {
			left: {
				onClick: (e) => {
					e.preventDefault()
					if (!flexyInstance) {
						return
					}

					flexyInstance.sliderArrows.navigate('left')
				},
			},

			right: {
				onClick: (e) => {
					e.preventDefault()

					if (!flexyInstance) {
						return
					}

					flexyInstance.sliderArrows.navigate('right')
				},
			},
		},
	}
}
