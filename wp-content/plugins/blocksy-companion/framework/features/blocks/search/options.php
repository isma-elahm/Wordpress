<?php

$is_pro = function_exists('blc_fs') && blc_fs()->can_use_premium_code();

$cpt_choices = [
	'post' => __('Posts', 'blocksy-companion'),
	'page' => __('Pages', 'blocksy-companion'),
];

$cpt_options = [
	'post' => true,
	'page' => true
];

if (class_exists('WooCommerce')) {
	$cpt_choices['product'] = __('Products', 'blocksy-companion');
	$cpt_options['product'] = true;
}

$all_cpts = blocksy_manager()->post_types->get_supported_post_types();

if (function_exists('is_bbpress')) {
	$all_cpts[] = 'forum';
	$all_cpts[] = 'topic';
	$all_cpts[] = 'reply';
}

foreach ($all_cpts as $single_cpt) {
	if (get_post_type_object($single_cpt)) {
		$cpt_choices[$single_cpt] = get_post_type_labels(
			get_post_type_object($single_cpt)
		)->singular_name;
	} else {
		$cpt_choices[$single_cpt] = ucfirst($single_cpt);
	}

	$cpt_options[$single_cpt] = false;
}

$is_pro = function_exists('blc_fs') && blc_fs()->can_use_premium_code();

$options = [

	[
		'buttonUseText' => [
			'label' => __( 'Placeholder Text', 'blocksy-companion' ),
			'type' => 'hidden',
			'value' => 'no',
		],

		'buttonPosition' => [
			'label' => __( 'Placeholder Text', 'blocksy-companion' ),
			'type' => 'hidden',
			'value' => 'inside',
		],

		'search_box_button_text' => [
			'label' => __( 'Button Text', 'blocksy-companion' ),
			'type' => 'hidden',
			'value' => __( 'Search', 'blocksy-companion' ),
		],

		'search_box_placeholder' => [
			'label' => __( 'Placeholder Text', 'blocksy-companion' ),
			'type' => 'hidden',
			'value' => __( 'Search', 'blocksy-companion' ),
		],

		'searchBoxHeight' => [
			'label' => __( 'Input Height', 'blocksy-companion' ),
			'type' => 'ct-slider',
			'min' => 40,
			'max' => 80,
			'value' => '',
			'responsive' => false,
			'divider' => 'top:full',
			'setting' => [ 'transport' => 'postMessage' ],
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [ 'buttonUseText' => 'no' ],
			'options' => $is_pro ? [
				'icon' => [
					'type' => 'icon-picker',
					'label' => __('Icon', 'blocksy-companion'),
					'design' => 'inline',
					'divider' => 'top:full',
					'value' => [
						'icon' => 'blc blc-search'
					]
				],
			] : []
		],

		'enable_live_results' => [
			'label' => __( 'Live Results', 'blocksy-companion' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'divider' => 'top:full',
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [ 'enable_live_results' => 'yes' ],
			'options' => [

				'live_results_images' => [
					'label' => __( 'Live Results Images', 'blocksy-companion' ),
					'type' => 'ct-switch',
					'value' => 'yes',
				],

				blocksy_rand_md5() => [
					'type' => 'ct-condition',
					'condition' => [ 'search_through/product' => true ],
					'options' => [
						'searchProductPrice' => [
							'label' => __( 'Live Results Product Price', 'blocksy-companion' ),
							'type' => 'ct-switch',
							'value' => 'no',
							'setting' => [ 'transport' => 'postMessage' ],
						],

						'searchProductStatus' => [
							'label' => __( 'Live Results Product Status', 'blocksy-companion' ),
							'type' => 'ct-switch',
							'value' => 'no',
							'setting' => [ 'transport' => 'postMessage' ],
						],
					]
				],

			],
		],

		'has_taxonomy_filter' => [
			'label' => __( 'Taxonomy Filter', 'blocksy-companion' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'divider' => 'top:full',
		],

		'taxonomy_filter_label' => [
			'label' => __( 'Placeholder Text', 'blocksy-companion' ),
			'type' => 'hidden',
			'value' => __('Select Category', 'blocksy-companion')
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [ 'has_taxonomy_filter' => 'yes' ],
			'options' => [

				'taxonomy_filter_visibility' => [
					'label' => __( 'Filter Visibility', 'blocksy-companion' ),
					'type' => 'ct-visibility',
					'design' => 'block',
					// 'allow_empty' => true,
					'setting' => [ 'transport' => 'postMessage' ],
					'value' => blocksy_default_responsive_value([
						'desktop' => true,
						'tablet' => true,
						'mobile' => false,
					]),

					'choices' => blocksy_ordered_keys([
						'desktop' => __( 'Desktop', 'blocksy-companion' ),
						'tablet' => __( 'Tablet', 'blocksy-companion' ),
						'mobile' => __( 'Mobile', 'blocksy-companion' ),
					]),
				],

				'has_taxonomy_children' => [
					'label' => __( 'Taxonomy Children', 'blocksy-companion' ),
					'type' => 'ct-switch',
					'value' => 'no',
					// 'divider' => 'top',
				],
			],
		],

		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __('Search Through Criteria', 'blocksy-companion'),
			'attr' => ['class' => 'components-base-control ct-title'],
			'desc' => __(
				'Chose in which post types do you want to perform searches.',
				'blocksy-companion'
			)
		],

		'search_through' => [
			'label' => false,
			'type' => 'ct-checkboxes',
			'attr' => ['data-columns' => '2'],
			'disableRevertButton' => true,
			'choices' => blocksy_ordered_keys($cpt_choices),
			'value' => $cpt_options
		],
	],

	$is_pro ? [
		'search_through_taxonomy' => [
			'label' => __('Search Through Taxonomies', 'blocksy-companion'),
			'type' => 'ct-switch',
			'value' => 'no',
			'divider' => 'top:full',
			'desc' => __('Search through taxonomies from selected custom post types.', 'blocksy-companion'),
		]
	] : []

];
