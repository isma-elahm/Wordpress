import {
	handleBackgroundOptionFor,
	responsiveClassesFor,
	typographyOption,
} from 'blocksy-customizer-sync'
import ctEvents from 'ct-events'

ctEvents.on(
	'ct:customizer:sync:collect-variable-descriptors',
	(allVariables) => {
		allVariables.result = {
			trending_block_thumbnails_width: {
				selector: '.ct-trending-block-item',
				variable: 'trending-block-image-width',
				responsive: true,
				unit: 'px',
			},

			trendingItemsVerticalAlignment: {
				selector: '.ct-trending-block-item',
				variable: 'vertical-alignment',
				responsive: true,
				unit: '',
			},

			...typographyOption({
				id: 'trendingBlockHeadingFont',
				selector: '.ct-trending-block .ct-module-title',
			}),

			trendingBlockHeadingFontColor: {
				selector: '.ct-trending-block .ct-module-title',
				variable: 'theme-heading-color',
				type: 'color',
				responsive: true,
			},

			...typographyOption({
				id: 'trendingBlockPostsFont',
				selector: '.ct-trending-block-item .ct-post-title',
			}),

			trendingBlockFontColor: [
				{
					selector: '.ct-trending-block-item .ct-post-title',
					variable: 'theme-link-initial-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '.ct-trending-block-item .ct-post-title',
					variable: 'theme-link-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			...typographyOption({
				id: 'trendingBlockTaxonomyFont',
				selector: '.ct-trending-block-item-content .entry-meta',
			}),



			trending_categories_font_colors: [
				{
					selector: '.ct-trending-block-item-content .entry-meta',
					variable: 'theme-link-initial-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '.ct-trending-block-item-content .entry-meta',
					variable: 'theme-link-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			trending_categories_button_type_font_colors: [
				{
					selector: '.ct-trending-block-item-content [data-type="pill"]',
					variable: 'theme-button-text-initial-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '.ct-trending-block-item-content [data-type="pill"]',
					variable: 'theme-button-text-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			trending_categories_button_type_background_colors: [
				{
					selector: '.ct-trending-block-item-content [data-type="pill"]',
					variable: 'theme-button-background-initial-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '.ct-trending-block-item-content [data-type="pill"]',
					variable: 'theme-button-background-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],



			...typographyOption({
				id: 'trendingBlockPriceFont',
				selector: '.ct-trending-block-item-content .price',
			}),

			trendingBlockPriceFontColor: {
				selector: '.ct-trending-block-item-content .price',
				variable: 'theme-text-color',
				type: 'color',
				responsive: true,
			},

			trendingBlockImageRadius: {
				selector: '.ct-trending-block-item',
				type: 'spacing',
				variable: 'trending-block-image-radius',
				responsive: true,
			},

			trendingBlockArrowsColor: [
				{
					selector: '.ct-trending-block [class*="ct-arrow"]',
					variable: 'theme-text-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '.ct-trending-block [class*="ct-arrow"]',
					variable: 'theme-link-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			...handleBackgroundOptionFor({
				id: 'trending_block_background',
				selector: '.ct-trending-block',
				responsive: true,
			}),

			...allVariables.result,
			trendingBlockContainerSpacing: {
				selector: '.ct-trending-block',
				variable: 'padding',
				responsive: true,
				unit: '',
			},
		}
	}
)

wp.customize('trending_block_visibility', (value) =>
	value.bind((to) =>
		responsiveClassesFor(
			'trending_block_visibility',
			document.querySelector('.ct-trending-block')
		)
	)
)

wp.customize('trending_block_label', (value) =>
	value.bind((to) => {
		const title = document.querySelector(
			'.ct-trending-block .ct-module-title'
		)

		if (title) {
			const components = title.innerHTML.split('<svg')
			components[0] = to
			title.innerHTML = components.join('<svg')
		}
	})
)
