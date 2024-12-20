<?php

$options = [];

$has_woo = class_exists('WooCommerce');
$woo_rules = [];

$brands_enabled = taxonomy_exists('product_brands');

if ($filter === 'all') {
	$woo_rules = array_merge(
		[
			[
				'id' => 'woo_shop',
				'title' => __('Shop Home', 'blocksy-companion')
			],
	
			[
				'id' => 'single_product',
				'title' => __('Single Product', 'blocksy-companion')
			],
	
			[
				'id' => 'all_product_archives',
				'title' => __('Product Archives', 'blocksy-companion')
			],
	
			[
				'id' => 'all_product_categories',
				'title' => __('Product Categories', 'blocksy-companion')
			],
	
			[
				'id' => 'all_product_attributes',
				'title' => __('Product Attributes', 'blocksy-companion')
			],
		],
		(
			$brands_enabled ? [
				[
					'id' => 'all_product_brands',
					'title' => __('Product Brands', 'blocksy-companion')
				]
			] : []
		),
		[
			[
				'id' => 'all_product_tags',
				'title' => __('Product Tags', 'blocksy-companion')
			],
	
			[
				'id' => 'product_ids',
				'title' => __('Single Product ID', 'blocksy-companion')
			],
	
			[
				'id' => 'product_with_taxonomy_ids',
				'title' => __('Single Product with Taxonomy ID', 'blocksy-companion'),
				'post_type' => 'product'
			],
	
			[
				'id' => 'product_taxonomy_ids',
				'title' => __('Taxonomy ID', 'blocksy-companion')
			],
		]
	);
}

if ($filter === 'product_tabs') {
	$woo_rules = [
		[
			'id' => 'product_ids',
			'title' => __('Product ID', 'blocksy-companion')
		],

		[
			'id' => 'product_with_taxonomy_ids',
			'title' => __('Product with Taxonomy ID', 'blocksy-companion'),
			'post_type' => 'product'
		]
	];
}

if ($filter === 'product_waitlist') {
	$woo_rules = [
		[
			'id' => 'product_ids',
			'title' => __('Product ID', 'blocksy-companion')
		],

		[
			'id' => 'product_with_taxonomy_ids',
			'title' => __('Product with Taxonomy ID', 'blocksy-companion'),
			'post_type' => 'product'
		]
	];
}

if ($has_woo) {
	$options = [
		[
			'title' => __('WooCommerce', 'blocksy-companion'),
			'rules' => $woo_rules
		]
	];
}
