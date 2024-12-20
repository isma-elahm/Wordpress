<?php

$rules = [
	[
		'id' => 'card_post_with_taxonomy_ids',
		'title' => __('Archive Item with Taxonomy ID', 'blocksy-companion'),
	]
];

if (function_exists('is_shop')) {
	$rules[] = [
		'id' => 'card_product_with_taxonomy_ids',
		'title' => __('WooCommerce Archive Item with Taxonomy ID', 'blocksy-companion'),
	];
}

$options = [
	[
		'title' => __('Archive Loop Speciffic', 'blocksy-companion'),
		'rules' => $rules
	]
];
