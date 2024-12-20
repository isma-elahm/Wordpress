import { createElement, useState } from '@wordpress/element'
import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'

import classnames from 'classnames'

import { useSelect } from '@wordpress/data'
import { store as coreStore } from '@wordpress/core-data'

function getMediaSourceUrlBySizeSlug(media, slug) {
	return media?.media_details?.sizes?.[slug]?.source_url || media?.source_url
}

const TermImagePreview = ({
	termImage,
	termIcon,

	attributes,
	attributes: {
		aspectRatio,
		imageFit,
		width,
		height,
		imageAlign,
		has_field_link,
		sizeSlug,
		image_hover_effect,
		imageSource,
	},
}) => {
	const [isLoaded, setIsLoaded] = useState(false)

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

	const maybeImageId =
		imageSource === 'icon'
			? termIcon?.attachment_id
			: termImage?.attachment_id

	const { media } = useSelect(
		(select) => {
			const { getMedia } = select(coreStore)

			return {
				media:
					maybeImageId &&
					getMedia(maybeImageId, {
						context: 'view',
					}),
			}
		},
		[maybeImageId]
	)

	const maybeUrl = getMediaSourceUrlBySizeSlug(media, sizeSlug)

	const imageStyles = {
		height: aspectRatio ? '100%' : height,
		width: !!aspectRatio && '100%',
		objectFit: imageFit,
		aspectRatio,
	}

	if (!maybeUrl) {
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
							vector-effect="non-scaling-stroke"
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
			onLoad={() => setIsLoaded(true)}
			loading="lazy"
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

	if (has_field_link && !isLoaded) {
		content = <a href="#">{content}</a>
	}

	return <figure {...blockProps}>{content}</figure>
}

export default TermImagePreview
