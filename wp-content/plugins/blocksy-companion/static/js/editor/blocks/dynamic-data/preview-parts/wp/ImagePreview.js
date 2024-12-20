import { createElement, useState } from '@wordpress/element'
import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'

import classnames from 'classnames'

import { useSelect } from '@wordpress/data'
import { useEntityProp, store as coreStore } from '@wordpress/core-data'

function getMediaSourceUrlBySizeSlug(media, slug) {
	return media?.media_details?.sizes?.[slug]?.source_url || media?.source_url
}

const VideoIndicator = () => (
	<span className="ct-video-indicator">
		<svg width="40" height="40" viewBox="0 0 40 40" fill="#fff">
			<path
				className="ct-play-path"
				d="M20,0C8.9,0,0,8.9,0,20s8.9,20,20,20s20-9,20-20S31,0,20,0z M16,29V11l12,9L16,29z"></path>
		</svg>
	</span>
)

const ImagePreview = ({
	postType,
	postId,

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
		videoThumbnail,
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

	const [featuredImage, setFeaturedImage] = useEntityProp(
		'postType',
		postType,
		'featured_media',
		postId
	)

	const { media } = useSelect(
		(select) => {
			const { getMedia } = select(coreStore)

			return {
				media:
					featuredImage &&
					getMedia(featuredImage, {
						context: 'view',
					}),
			}
		},
		[featuredImage]
	)

	const maybeUrl = getMediaSourceUrlBySizeSlug(media, sizeSlug)

	const imageStyles = {
		height: aspectRatio ? '100%' : height,
		width: !!aspectRatio && '100%',
		objectFit: imageFit,
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

	const hasInnerContent =
		(media.has_video && videoThumbnail === 'yes') ||
		image_hover_effect !== 'none'

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

				{media.has_video && videoThumbnail === 'yes' ? (
					<VideoIndicator />
				) : null}
			</span>
		)
	}

	if (
		has_field_link &&
		!media.has_video &&
		videoThumbnail !== 'yes' &&
		!isLoaded
	) {
		content = <a href="#">{content}</a>
	}

	return <figure {...blockProps}>{content}</figure>
}

export default ImagePreview
