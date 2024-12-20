<?php

$options = [
	'has_shop_sort' => [
		'label' => __( 'Products Sorting', 'blocksy' ),
		'type' => 'ct-panel',
		'switch' => true,
		'value' => 'yes',
		'sync' => blocksy_sync_whole_page([
			'prefix' => 'woo_categories',
			'loader_selector' => '.woo-listing-top'
		]),
		'inner-options' => [

			'shop_sort_visibility' => [
				'label' => __('Visibility', 'blocksy'),
				'type' => 'ct-visibility',
				'design' => 'block',
				'divider' => 'top',
				'setting' => ['transport' => 'postMessage'],
				'allow_empty' => true,
				'value' => blocksy_default_responsive_value([
					'desktop' => true,
					'tablet' => true,
					'mobile' => true,
				]),
				'choices' => blocksy_ordered_keys([
					'desktop' => __( 'Desktop', 'blocksy' ),
					'tablet' => __( 'Tablet', 'blocksy' ),
					'mobile' => __( 'Mobile', 'blocksy' ),
				]),
			],

		],
	],
];