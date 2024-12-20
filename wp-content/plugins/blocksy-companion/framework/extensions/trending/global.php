<?php

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-trending-block-item',
	'variableName' => 'trending-block-image-width',
	'responsive' => true,
	'unit' => 'px',
	'value' => blocksy_get_theme_mod('trending_block_thumbnails_width', '60')
]);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-trending-block-item',
	'variableName' => 'vertical-alignment',
	'value' => blocksy_get_theme_mod( 'trendingItemsVerticalAlignment', 'center' ),
	'unit' => '',
]);

blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod( 'trendingBlockHeadingFont',
		blocksy_typography_default_values([
			'size' => '15px',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-trending-block .ct-module-title',
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('trendingBlockHeadingFontColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => '.ct-trending-block .ct-module-title',
			'variable' => 'theme-heading-color'
		],
	],
	'responsive' => true,
]);


blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod( 'trendingBlockPostsFont',
		blocksy_typography_default_values([
			'size' => '15px',
			'variation' => 'n5',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-trending-block-item .ct-post-title',
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('trendingBlockFontColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-text-color)' ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => '.ct-trending-block-item .ct-post-title',
			'variable' => 'theme-link-initial-color'
		],

		'hover' => [
			'selector' => '.ct-trending-block-item .ct-post-title',
			'variable' => 'theme-link-hover-color'
		],
	],
	'responsive' => true,
]);


blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod( 'trendingBlockTaxonomyFont',
		blocksy_typography_default_values([
			'size' => '13px',
			'variation' => 'n5',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-trending-block-item-content .entry-meta',
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('trendingBlockTaxonomyFontColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-text-color)' ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => '.ct-trending-block-item-content .entry-meta',
			'variable' => 'theme-link-initial-color'
		],

		'hover' => [
			'selector' => '.ct-trending-block-item-content .entry-meta',
			'variable' => 'theme-link-hover-color'
		],
	],
	'responsive' => true,
]);



blocksy_output_colors([
	'value' => blocksy_get_theme_mod('trending_categories_font_colors'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-text-color)' ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '.ct-trending-block-item-content .entry-meta',
			'variable' => 'theme-link-initial-color'
		],

		'hover' => [
			'selector' => '.ct-trending-block-item-content .entry-meta',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('trending_categories_button_type_font_colors'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '.ct-trending-block-item-content [data-type="pill"]',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.ct-trending-block-item-content [data-type="pill"]',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('trending_categories_button_type_background_colors'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '.ct-trending-block-item-content [data-type="pill"]',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.ct-trending-block-item-content [data-type="pill"]',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);


blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod( 'trendingBlockPriceFont',
		blocksy_typography_default_values([
			'size' => '13px',
			// 'variation' => 'n5',a
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-trending-block-item-content .price',
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('trendingBlockPriceFontColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => '.ct-trending-block-item-content .price',
			'variable' => 'theme-text-color'
		],
	],
	'responsive' => true,
]);


blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-trending-block-item',
	'property' => 'trending-block-image-radius',
	'value' => blocksy_get_theme_mod(
		'trendingBlockImageRadius',
		blocksy_spacing_value()
	)
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('trendingBlockArrowsColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => '.ct-trending-block [class*="ct-arrow"]',
			'variable' => 'theme-text-color'
		],

		'hover' => [
			'selector' => '.ct-trending-block [class*="ct-arrow"]',
			'variable' => 'theme-link-hover-color'
		],
	],
	'responsive' => true,
]);

blocksy_output_background_css([
	'selector' => '.ct-trending-block',
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'value' => blocksy_get_theme_mod(
		'trending_block_background',
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => 'var(--theme-palette-color-5)'
				],
			],
		])
	),
	'responsive' => true,
]);

$container_inner_spacing = blocksy_get_theme_mod( 'trendingBlockContainerSpacing', '30px' );

if ($container_inner_spacing !== '30px') {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => ".ct-trending-block",
		'variableName' => 'padding',
		'value' => $container_inner_spacing,
		'unit' => ''
	]);
}
