import {
	applyPrefixFor,
	watchOptionsWithPrefix,
	getOptionFor,
	getPrefixFor,
	disableTransitionsStart,
	disableTransitionsEnd,
	setRatioFor,
} from '../../helpers'
import { renderSingleEntryMeta } from '../../helpers/entry-meta'
import { handleBackgroundOptionFor } from '../../variables/background'
import { typographyOption } from '../../variables/typography'
import { getSingleShareBoxVariables } from './share-box'

import { maybePromoteScalarValueIntoResponsive } from 'customizer-sync-helpers/dist/promote-into-responsive'

let prefix = getPrefixFor()

export const getSingleElementsVariables = () => ({
	...getSingleShareBoxVariables(),

	// featured image
	[`${prefix}_featured_image_border_radius`]: {
		selector: applyPrefixFor('.ct-featured-image', prefix),
		type: 'spacing',
		variable: 'theme-border-radius',
		responsive: true,
	},

	// autor Box
	[`${prefix}_single_author_box_spacing`]: {
		selector: applyPrefixFor('.author-box', prefix),
		variable: 'spacing',
		responsive: true,
		unit: '',
	},

	...typographyOption({
		id: `${prefix}_single_author_box_name_font`,
		selector: applyPrefixFor('.author-box .author-box-name', prefix),
	}),

	[`${prefix}_single_author_box_name_color`]: {
		selector: applyPrefixFor('.author-box .author-box-name', prefix),
		variable: 'theme-heading-color',
		type: 'color:default',
		responsive: true,
	},

	...typographyOption({
		id: `${prefix}_single_author_box_font`,
		selector: applyPrefixFor('.author-box .author-box-bio', prefix),
	}),

	[`${prefix}_single_author_box_font_color`]: [
		{
			selector: applyPrefixFor('.author-box .author-box-bio', prefix),
			variable: 'theme-text-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: applyPrefixFor('.author-box .author-box-bio', prefix),
			variable: 'theme-link-initial-color',
			type: 'color:initial',
			responsive: true,
		},

		{
			selector: applyPrefixFor('.author-box .author-box-bio', prefix),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	[`${prefix}_single_author_box_social_icons_color`]: [
		{
			selector: applyPrefixFor('.author-box .author-box-socials', prefix),
			variable: 'theme-icon-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: applyPrefixFor('.author-box .author-box-socials', prefix),
			variable: 'theme-icon-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	[`${prefix}_single_author_box_social_icons_background`]: [
		{
			selector: applyPrefixFor('.author-box .author-box-socials', prefix),
			variable: 'background-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: applyPrefixFor('.author-box .author-box-socials', prefix),
			variable: 'background-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	...handleBackgroundOptionFor({
		id: `${prefix}_single_author_box_container_background`,
		selector: applyPrefixFor('.author-box[data-type="type-1"]', prefix),
		responsive: true,
	}),

	[`${prefix}_single_author_box_shadow`]: {
		selector: applyPrefixFor('.author-box[data-type="type-1"]', prefix),
		type: 'box-shadow',
		variable: 'theme-box-shadow',
		responsive: true,
	},

	[`${prefix}_single_author_box_container_border`]: {
		selector: applyPrefixFor('.author-box[data-type="type-1"]', prefix),
		variable: 'theme-border',
		type: 'border',
		responsive: true,
		// skip_none: true,
	},

	[`${prefix}_single_author_box_border_radius`]: {
		selector: applyPrefixFor('.author-box[data-type="type-1"]', prefix),
		type: 'spacing',
		variable: 'theme-border-radius',
		responsive: true,
	},

	[`${prefix}_single_author_box_border`]: {
		selector: applyPrefixFor('.author-box[data-type="type-2"]', prefix),
		variable: 'theme-border-color',
		type: 'color',
		responsive: true,
	},

	// post tags
	[`${prefix}_post_tags_alignment`]: {
		selector: applyPrefixFor('.entry-tags', prefix),
		variable: 'horizontal-alignment',
		responsive: true,
		unit: '',
	},

	...typographyOption({
		id: `${prefix}_post_tags_title_font`,
		selector: applyPrefixFor('.entry-tags .ct-module-title', prefix),
	}),

	[`${prefix}_post_tags_title_color`]: {
		selector: applyPrefixFor('.entry-tags .ct-module-title', prefix),
		variable: 'theme-heading-color',
		type: 'color:default',
	},

	[`${prefix}_post_tags_border_radius`]: {
		selector: applyPrefixFor('.entry-tags-items', prefix),
		type: 'spacing',
		variable: 'theme-border-radius',
		responsive: true,
	},


	// posts navigation
	[`${prefix}_post_nav_spacing`]: {
		selector: applyPrefixFor('.post-navigation', prefix),
		variable: 'margin',
		responsive: true,
		unit: '',
	},

	[`${prefix}_posts_nav_font_color`]: [
		{
			selector: applyPrefixFor('.post-navigation', prefix),
			variable: 'theme-link-initial-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.post-navigation', prefix),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_posts_nav_image_overlay_color`]: {
		selector: applyPrefixFor('.post-navigation', prefix),
		variable: 'image-overlay-color',

		type: 'color:hover',
	},

	[`${prefix}_posts_nav_image_border_radius`]: {
		selector: applyPrefixFor('.post-navigation figure', prefix),
		type: 'spacing',
		variable: 'theme-border-radius',
		// responsive: true,
	},


	// related posts
	[`${prefix}_related_label_alignment`]: {
		selector: applyPrefixFor('.ct-related-posts .ct-module-title', prefix),
		variable: 'horizontal-alignment',
		responsive: true,
		unit: '',
	},

	...handleBackgroundOptionFor({
		id: `${prefix}_related_posts_background`,
		selector: applyPrefixFor('.ct-related-posts-container', prefix),
	}),

	[`${prefix}_related_posts_container_spacing`]: {
		selector: applyPrefixFor('.ct-related-posts-container', prefix),
		variable: 'padding',
		responsive: true,
		unit: '',
	},

	...typographyOption({
		id: `${prefix}_related_posts_label_font`,
		selector: applyPrefixFor('.ct-related-posts .ct-module-title', prefix),
	}),

	[`${prefix}_related_posts_label_color`]: {
		selector: applyPrefixFor('.ct-related-posts .ct-module-title', prefix),
		variable: 'theme-heading-color',
		type: 'color:default',
	},

	...typographyOption({
		id: `${prefix}_related_posts_link_font`,
		selector: applyPrefixFor(
			'.ct-related-posts .related-entry-title',
			prefix
		),
	}),

	[`${prefix}_related_posts_link_color`]: [
		{
			selector: applyPrefixFor(
				'.ct-related-posts .related-entry-title',
				prefix
			),
			variable: 'theme-heading-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor(
				'.ct-related-posts .related-entry-title',
				prefix
			),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},
	],

	...typographyOption({
		id: `${prefix}_related_posts_meta_font`,
		selector: applyPrefixFor('.ct-related-posts .entry-meta', prefix),
	}),

	[`${prefix}_related_posts_meta_color`]: [
		{
			selector: applyPrefixFor('.ct-related-posts .entry-meta', prefix),
			variable: 'theme-text-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.ct-related-posts .entry-meta', prefix),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_related_meta_button_type_font_colors`]: [
		{
			selector: applyPrefixFor('.ct-related-posts [data-type="pill"]', prefix),
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.ct-related-posts [data-type="pill"]', prefix),
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_related_meta_button_type_background_colors`]: [
		{
			selector: applyPrefixFor('.ct-related-posts [data-type="pill"]', prefix),
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.ct-related-posts [data-type="pill"]', prefix),
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_related_thumb_radius`]: {
		selector: applyPrefixFor(
			'.ct-related-posts .ct-media-container',
			prefix
		),
		type: 'spacing',
		variable: 'theme-border-radius',
		responsive: true,
		emptyValue: 5,
	},

	[`${prefix}_related_narrow_width`]: {
		selector: applyPrefixFor('.ct-related-posts-container', prefix),
		variable: 'theme-narrow-container-max-width',
		unit: 'px',
	},

	[`${prefix}_related_posts_columns`]: [
		{
			selector: applyPrefixFor('.ct-related-posts', prefix),
			variable: 'grid-template-columns',
			responsive: true,
			extractValue: (val) => {
				const responsive = maybePromoteScalarValueIntoResponsive(val)

				return {
					desktop: `repeat(${responsive.desktop}, minmax(0, 1fr))`,
					tablet: `repeat(${responsive.tablet}, minmax(0, 1fr))`,
					mobile: `repeat(${responsive.mobile}, minmax(0, 1fr))`,
				}
			},
		},
	],


	// related posts slider
	[`${prefix}_related_posts_slideshow_columns`]: [
		{
			selector: applyPrefixFor(
				'.ct-related-posts .flexy-container',
				prefix
			),
			variable: 'grid-columns-width',
			responsive: true,
			extractValue: (val) => {
				const responsive = maybePromoteScalarValueIntoResponsive(val)

				ctEvents.trigger('blocksy:frontend:init')
				setTimeout(() => {
					const sliders = document.querySelectorAll(
						'.ct-related-posts .flexy-container'
					)

					if (sliders.length) {
						sliders.forEach((slider) => {
							const firstChild = slider.querySelector(
								'.flexy-item:first-child'
							)

							if (slider.flexy) {
								slider.flexy.scheduleSliderRecalculation()
							}
						})
					}
				}, 50)

				return {
					desktop: `calc(100% / ${responsive.desktop})`,
					tablet: `calc(100% / ${responsive.tablet})`,
					mobile: `calc(100% / ${responsive.mobile})`,
				}
			},
		},
	],
})

watchOptionsWithPrefix({
	getPrefix: () => prefix,
	getOptionsForPrefix: ({ prefix }) => [`${prefix}_related_order`],

	render: ({ id }) => {
		if (id === `${prefix}_related_order`) {
			const relatedItemsEl = document.querySelectorAll(
				'.ct-related-posts-items'
			)

			disableTransitionsStart(relatedItemsEl)

			disableTransitionsEnd(relatedItemsEl)

			let relatedOrder = getOptionFor('related_order', prefix)
			disableTransitionsStart(relatedItemsEl)

			let allItemsToOutput = relatedOrder.filter(
				({ enabled }) => !!enabled
			)

			allItemsToOutput.map((component, index) => {
				;[
					...document.querySelectorAll(
						'.ct-related-posts-items > article'
					),
				].map((article) => {
					let image = article.querySelector('.ct-media-container')

					if (component.id === 'featured_image' && image) {
						setRatioFor({
							ratio: component.thumb_ratio,
							el: image,
						})
					}

					if (component.id === 'post_meta') {
						let moreDefaults = {}
						let el = article.querySelectorAll('.entry-meta')

						if (
							relatedOrder.filter(({ id }) => id === 'post_meta')
								.length > 1
						) {
							if (
								relatedOrder
									.filter(({ id }) => id === 'post_meta')
									.map(({ __id }) => __id)
									.indexOf(component.__id) === 0
							) {
								moreDefaults = {
									meta_elements: [
										{
											id: 'categories',
											enabled: true,
										},
									],
								}

								el = el[0]
							}

							if (
								relatedOrder
									.filter(({ id }) => id === 'post_meta')
									.map(({ __id }) => __id)
									.indexOf(component.__id) === 1
							) {
								moreDefaults = {
									meta_elements: [
										{
											id: 'author',
											enabled: true,
										},

										{
											id: 'post_date',
											enabled: true,
										},

										{
											id: 'comments',
											enabled: true,
										},
									],
								}

								if (el.length > 1) {
									el = el[1]
								}
							}
						}

						if (el.length === 1) {
							el = el[0]
						}

						renderSingleEntryMeta({
							el,
							...moreDefaults,
							...component,
						})
					}
				})
			})

			disableTransitionsEnd(relatedItemsEl)
		}
	},
})

export const getPostRelatedVariables = () => ({
	[`${prefix}_related_order`]: (v) => {
		let variables = []

		v.map((layer) => {
			// bottom spacing
			let selectorsMap = {
				title: '.ct-related-posts .related-entry-title',
				featured_image: '.ct-related-posts .ct-media-container',
			}

			if (selectorsMap[layer.id]) {
				variables = [
					...variables,
					{
						selector: applyPrefixFor(
							selectorsMap[layer.id],
							prefix
						),
						variable: 'card-element-spacing',
						responsive: true,
						unit: 'px',
						extractValue: () => {
							let defaultValue = 20

							if (layer.id === 'title') {
								defaultValue = 5
							}

							return layer.spacing || defaultValue
						},
					},
				]
			}

			if (layer.id === 'post_meta') {
				variables = [
					...variables,
					{
						selector: applyPrefixFor(
							`.ct-related-posts .entry-meta[data-id="${(
								layer?.__id || 'default'
							).slice(0, 6)}"]`,
							prefix
						),
						variable: 'card-element-spacing',
						responsive: true,
						unit: 'px',
						extractValue: () => {
							return layer.spacing || 20
						},
					},
				]
			}

			if (layer.id === 'content-block') {
				variables = [
					...variables,
					{
						selector: applyPrefixFor(
							`.ct-related-posts .ct-entry-content-block[data-id="${
								layer?.__id || 'default'
							}"]`,
							prefix
						),
						variable: 'card-element-spacing',
						responsive: true,
						unit: 'px',
						extractValue: () => {
							return layer.spacing || 20
						},
					},
				]
			}

			if (
				[
					'acf_field',
					'metabox_field',
					'toolset_field',
					'jetengine_field',
					'custom_field',
					'pods_field',
				].includes(layer.id)
			) {
				variables = [
					...variables,
					{
						selector: applyPrefixFor(
							`.ct-related-posts .ct-dynamic-data-layer[data-field*=":${(
								layer?.__id || 'default'
							).slice(0, 6)}"]`,
							prefix
						),
						variable: 'card-element-spacing',
						responsive: true,
						unit: 'px',
						extractValue: () => {
							return layer.spacing || 20
						},
					},
				]
			}
		})

		return [...variables]
	},
})
