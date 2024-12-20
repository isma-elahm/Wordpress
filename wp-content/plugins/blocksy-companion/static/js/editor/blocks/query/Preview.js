import {
	useState,
	useEffect,
	createElement,
	useMemo,
	RawHTML,
} from '@wordpress/element'

import { withSelect, withDispatch } from '@wordpress/data'
import { Spinner } from '@wordpress/components'

import { __ } from 'ct-i18n'

import { usePostsBlockData } from './hooks/use-posts-block-data'
import { useFlexySlider } from '../../hooks/use-flexy-slider'

import { getStylesForBlock } from '../../utils/css'

import classnames from 'classnames'

import parse, { domToReact, attributesToProps } from 'html-react-parser'

const getReactElement = ({ isSlideshow, sliderDescriptor, blockData }) => {
	if (!isSlideshow) {
		return <RawHTML>{blockData.customizer_preview}</RawHTML>
	}

	const options = {
		replace: ({ attribs, children, name }, index) => {
			if (
				attribs &&
				attribs.class &&
				attribs.class.includes('flexy-container')
			) {
				return (
					<div
						{...attributesToProps(attribs)}
						{...sliderDescriptor.flexyContainerAttr}>
						{domToReact(children, options)}
					</div>
				)
			}

			if (
				attribs &&
				attribs.class &&
				attribs.class.includes('flexy-arrow-')
			) {
				const TagName = name

				const arrowDescriptor = attribs.class.includes(
					'flexy-arrow-next'
				)
					? sliderDescriptor.arrowsDescritor.right
					: sliderDescriptor.arrowsDescritor.left

				return (
					<TagName
						{...attributesToProps(attribs)}
						{...arrowDescriptor}>
						{domToReact(children, options)}
					</TagName>
				)
			}

			const className =
				attribs && attribs.class ? attribs.class.split(' ') : []

			if (className.includes('flexy-item')) {
				let maybeElementDescriptor =
					sliderDescriptor.elementDescriptorForIndex(index)

				if (maybeElementDescriptor) {
					maybeElementDescriptor = maybeElementDescriptor.attr
				}

				let { className, ...props } = attributesToProps(attribs)

				if (maybeElementDescriptor) {
					const {
						className: maybeElementClassName,
						...maybeElementProps
					} = maybeElementDescriptor

					className = classnames(className, maybeElementClassName)

					props = {
						...props,
						...maybeElementProps,
					}
				}

				return (
					<div className={classnames(className)} {...props}>
						{domToReact(children, options)}
					</div>
				)
			}
		},
	}

	return parse(blockData.customizer_preview, options)
}

const Preview = ({ attributes, postId, uniqueId }) => {
	const { blockData } = usePostsBlockData({
		attributes,
		previewedPostId: postId,
	})

	const isSlideshow = attributes.has_slideshow === 'yes'

	const sliderDescriptor = useFlexySlider({
		isSlideshow,
		attributes,
		toWatch: blockData ? blockData.customizer_preview : {},
	})

	const blockStyles = getStylesForBlock([])

	if (!blockData) {
		return <Spinner />
	}

	if (!blockData.customizer_preview) {
		return <p>{__('No results found.', 'blocksy-companion')}</p>
	}

	return (
		<>
			{getReactElement({ isSlideshow, sliderDescriptor, blockData })}

			{blockStyles}
		</>
	)
}

export default Preview
