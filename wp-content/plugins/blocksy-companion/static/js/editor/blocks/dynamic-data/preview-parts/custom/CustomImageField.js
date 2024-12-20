import { createElement } from '@wordpress/element'

import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'

import classnames from 'classnames'

const CustomImageField = ({
	fieldDescriptor,
	fieldData,

	attributes,
	attributes: {
		aspectRatio,
		width,
		height,
		imageAlign,
		image_hover_effect,
		sizeSlug,
	},

	postId,
}) => {
	const borderProps = useBorderProps(attributes)

	const blockProps = useBlockProps({
		className: classnames('ct-dynamic-media', {
			[`align${imageAlign}`]: imageAlign,
		}),

		style: {
			width,
			height,
		},
	})

	let maybeUrl = fieldData?.value?.url

	if (fieldData?.value?.sizes?.[sizeSlug]) {
		if (typeof fieldData.value.sizes[sizeSlug] === 'string') {
			maybeUrl = fieldData.value.sizes[sizeSlug]
		} else {
			maybeUrl = fieldData.value.sizes[sizeSlug].url
		}
	}

	const imageStyles = {
		height: aspectRatio ? '100%' : height,
		width: !!aspectRatio && '100%',
		objectFit: !!(height || aspectRatio) && 'cover',
		aspectRatio,
	}

	if (!maybeUrl || !postId) {
		return (
			<figure {...blockProps}>
				<div
					className="ct-dynamic-data-placeholder"
					style={{
						aspectRatio,
					}}>
					<svg
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 60 60"
						preserveAspectRatio="none"
						className="ct-dynamic-data-placeholder-illustration"
						aria-hidden="true"
						focusable="false">
						<path
							vectorEffect="non-scaling-stroke"
							d="M60 60 0 0"></path>
					</svg>
				</div>
			</figure>
		)
	}

	const hasInnerContent = image_hover_effect !== 'none'

	let content = (
		<img
			className={!hasInnerContent ? borderProps.className : ''}
			style={{
				...(!hasInnerContent ? borderProps.style : {}),
				...imageStyles,
			}}
			src={maybeUrl}
		/>
	)

	if (hasInnerContent) {
		content = (
			<span
				data-hover={image_hover_effect}
				className={`ct-dynamic-media-inner ${borderProps.className}`}
				style={{
					...borderProps.style,
				}}>
				{content}
			</span>
		)
	}

	return <figure {...blockProps}>{content}</figure>
}

export default CustomImageField
