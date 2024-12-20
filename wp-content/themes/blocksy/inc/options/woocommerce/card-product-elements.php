<?php

$card_additional_actions = apply_filters(
	'blocksy_woo_card_options:additional_actions',
	[
		// ['id' => '...', 'label' => '...']
	]
);

$card_additional_actions_design_options = [];
$card_actions_condition = [];

$card_additional_actions_options = apply_filters(
	'blocksy_woo_card_options:additional_options',
	[
		[
			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [
					'shop_cards_type' => 'type-3'
				],
				'options' => [
					blocksy_rand_md5() => [
						'type' => 'ct-title',
						'label' => __( 'Additional Actions', 'blocksy' ),
					]
				]
			],
	
			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [
					'shop_cards_type' => '!type-3'
				],
				'options' => ! empty($card_additional_actions) ? [
					blocksy_rand_md5() => [
						'type' => 'ct-title',
						'label' => __( 'Additional Actions', 'blocksy' ),
					]
				] : []
			]
		]
	]
);

if (! empty($card_additional_actions)) {
	foreach ($card_additional_actions as $single_action) {
		$card_actions_condition[$single_action['id']] = 'yes';

		$card_additional_actions_options[$single_action['id']] = [
			'label' => $single_action['label'],
			'type' => 'ct-switch',
			'value' => 'yes',
			'sync' => blocksy_sync_whole_page([
				'loader_selector' => '[data-products]'
			]),
		];
	}
}

$card_additional_actions_design_options[blocksy_rand_md5()] = [
	'type' => 'ct-condition',
	'condition' => [
		'any' => [
			'all' => [
				'has_archive_add_to_cart' => 'yes',
				'shop_cards_type' => 'type-3'
			],
			'any' => $card_actions_condition,
		]
	],
	'options' => [
		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __( 'Additional Actions', 'blocksy' ),
		],

		'additional_actions_button_icon_color' => [
			'label' => __( 'Icon Color', 'blocksy' ),
			'type'  => 'ct-color-picker',
			'design' => 'block:right',
			'responsive' => true,
			'setting' => [ 'transport' => 'postMessage' ],
			'value' => [
				'default' => [
					'color' => 'var(--theme-text-color)',
				],
				'hover' => [
					'color' => '#ffffff',
				],

				'default_2' => [
					'color' => 'var(--theme-text-color)',
				],
				'hover_2' => [
					'color' => 'var(--theme-palette-color-1)',
				],
			],

			'pickers' => [
				[
					'title' => __( 'Initial', 'blocksy' ),
					'id' => 'default',
					'condition' => [ 'shop_cards_type' => 'type-1|type-2' ]
				],
				[
					'title' => __( 'Hover/Active', 'blocksy' ),
					'id' => 'hover',
					'condition' => [ 'shop_cards_type' => 'type-1|type-2' ]
				],

				[
					'title' => __( 'Initial', 'blocksy' ),
					'id' => 'default_2',
					'condition' => [ 'shop_cards_type' => 'type-3' ]
				],
				[
					'title' => __( 'Hover/Active', 'blocksy' ),
					'id' => 'hover_2',
					'condition' => [ 'shop_cards_type' => 'type-3' ]
				],
			],
		],

		'additional_actions_button_background_color' => [
			'label' => __( 'Background Color', 'blocksy' ),
			'type'  => 'ct-color-picker',
			'design' => 'block:right',
			'responsive' => true,
			'setting' => [ 'transport' => 'postMessage' ],
			'value' => [
				'default' => [
					'color' => '#ffffff',
				],
				'hover' => [
					'color' => 'var(--theme-palette-color-1)',
				],

				'default_2' => [
					'color' => '#ffffff',
				],
				'hover_2' => [
					'color' => '#ffffff',
				],
			],

			'pickers' => [
				[
					'title' => __( 'Initial', 'blocksy' ),
					'id' => 'default',
					'condition' => [ 'shop_cards_type' => 'type-1|type-2' ]
				],
				[
					'title' => __( 'Hover/Active', 'blocksy' ),
					'id' => 'hover',
					'condition' => [ 'shop_cards_type' => 'type-1|type-2' ]
				],

				[
					'title' => __( 'Initial', 'blocksy' ),
					'id' => 'default_2',
					'condition' => [ 'shop_cards_type' => 'type-3' ]
				],
				[
					'title' => __( 'Hover/Active', 'blocksy' ),
					'id' => 'hover_2',
					'condition' => [ 'shop_cards_type' => 'type-3' ]
				],
			],
		],
	]
];

$taxonomies = array_values(array_diff(
	get_object_taxonomies('product'),
	[
		'post_format',
		'product_shipping_class',
	]
));

$taxonomies_options = [];

foreach ($taxonomies as $taxonomy) {
	$taxonomy_object = get_taxonomy($taxonomy);

	if (! $taxonomy_object->public) {
		continue;
	}

	$taxonomies_options[$taxonomy] = ucfirst(
		str_replace(
			'Product ',
			'',
			$taxonomy_object->label
		)
	);
}

$options = [
	'product_card_options_panel' => [
		'label' => __( 'Card Options', 'blocksy' ),
		'type' => 'ct-panel',
		'wrapperAttr' => [ 'data-panel' => 'only-arrow' ],
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [
					'woocommerce_archive_thumbnail_image_width' => [
						'type' => 'hidden',
						'label' => __('Image Width', 'blocksy'),
						'desc' => __('Image height will be automatically calculated based on the image ratio.', 'blocksy'),
						'value' => 500,
						'design' => 'inline',
						'setting' => [
							'type' => 'option',
							'capability' => 'manage_woocommerce',
							'transport' => 'postMessage'
						]
					],

					'woocommerce_archive_thumbnail_cropping' => [
						'label' => false,
						'type' => 'hidden',
						'value' => 'predefined',
						'setting' => [
							'type' => 'option',
							'capability' => 'manage_woocommerce',
							'transport' => 'postMessage'
						],
						'disableRevertButton' => true,
						'desc' => __('Width', 'blocksy'),
					],

					'woocommerce_archive_thumbnail_cropping_custom_width' => [
						'label' => false,
						'type' => 'hidden',
						'value' => 3,
						'setting' => [
							'type' => 'option',
							'capability' => 'manage_woocommerce',
							'transport' => 'postMessage'
						],
						'disableRevertButton' => true,
						'desc' => __('Width', 'blocksy'),
					],

					'woocommerce_archive_thumbnail_cropping_custom_height' => [
						'label' => false,
						'type' => 'hidden',
						'value' => 4,
						'setting' => [
							'type' => 'option',
							'capability' => 'manage_woocommerce',
							'transport' => 'postMessage'
						],
						'disableRevertButton' => true,
						'desc' => __('Height', 'blocksy'),
					],

					[
						'woo_card_layout' => [
							'label' => false,
							'type' => 'ct-layers',
							'manageable' => false,
							'divider' => 'bottom:full',
							'sync' => [
								blocksy_sync_whole_page([
									'prefix' => 'woo_categories',
									'loader_selector' => '[data-products] > li'
								]),
								
								blocksy_sync_whole_page([
									'id' => 'woo_card_layout_meta',
									'prefix' => 'woo_categories',
									'loader_selector' => '[data-products] > li .entry-meta'
								]),

								[
									'prefix' => 'woo_categories',
									'id' => 'woo_card_layout_skip',
									'loader_selector' => 'skip',
									'container_inclusive' => false
								],
							],
							'value' => blocksy_get_woo_archive_layout_defaults(),
							'settings' => apply_filters(
								'blocksy_woo_card_options_layers:extra',
								[
									'product_image' => [
										'label' => __('Product Image', 'blocksy'),
										'options' => [

											[
												'blocksy_woocommerce_archive_thumbnail_cropping' => [
													'label' => __('Image Ratio', 'blocksy'),
													'type' => 'ct-woocommerce-ratio',
													/**
													 * Can be
													 * 1:1
													 * custom
													 * predefined
													 */
													'value' => 'predefined',
													'view' => 'inline',
													'design' => 'block',
													'preview_width_key' => 'woocommerce_archive_thumbnail_image_width',
													'inner-options' => [

														'woocommerce_archive_thumbnail_image_width' => [
															'label' => __('Image Size', 'blocksy'),
															'type' => 'text',
															'value' => 500,
															'design' => 'block',
															'setting' => [
																'type' => 'option',
																'capability' => 'manage_woocommerce',
															],
															'desc' => __('Image height will be automatically calculated based on the image ratio.', 'blocksy'),
														],

													],

													'sync' => [
														'id' => 'woo_card_layout_skip'
													]
												],

												'product_image_hover' => [
													'label' => __( 'Hover Effect', 'blocksy' ),
													'type' => 'ct-select',
													'value' => 'none',
													'view' => 'text',
													'design' => 'block',
													'setting' => [ 'transport' => 'postMessage' ],
													'choices' => blocksy_ordered_keys(
														[
															'none' => __( 'None', 'blocksy' ),
															'swap' => __( 'Swap Images', 'blocksy' ),
															'zoom-in' => __( 'Zoom In', 'blocksy' ),
															'zoom-out' => __( 'Zoom Out', 'blocksy' ),
														]
													),

													'sync' => blocksy_sync_whole_page([
														'prefix' => 'woo_categories',
														'loader_selector' => '[data-products] > li'
													]),
												],
											],

											(
												function_exists('blc_fs')
												&&
												blc_fs()->can_use_premium_code()
											) ? [
												'has_archive_video_thumbnail' => [
													'label' => __( 'Video Thumbnail', 'blocksy' ),
													'type' => 'ct-switch',
													'value' => 'no',
													'sync' => blocksy_sync_whole_page([
														'prefix' => 'woo_categories',
														'loader_selector' => '[data-products] > li'
													]),
												],
											] : [],

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 25,
												'responsive' => true,

												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],

										],
									],

									'product_title' => [
										'label' => __('Title', 'blocksy'),
										'options' => [
											'heading_tag' => [
												'label' => __('Heading Tag', 'blocksy'),
												'type' => 'ct-select',
												'value' => 'h2',
												'view' => 'text',
												'design' => 'block',
												'choices' => blocksy_ordered_keys(
													[
														'h1' => 'H1',
														'h2' => 'H2',
														'h3' => 'H3',
														'h4' => 'H4',
														'h5' => 'H5',
														'h6' => 'H6',
														'p' => 'p',
														'span' => 'span',
													]
												),
											],

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 10,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],
										]
									],

									'product_price' => [
										'label' => __('Price', 'blocksy'),
										'condition' => [
											'shop_cards_type' => '!type-2'
										],
										'options' => [
											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 10,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],
										]
									],

									'product_rating' => [
										'label' => __('Star Rating', 'blocksy'),
										'options' => [
											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 10,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],
										]
									],

									'product_meta' => [
										'label' => __('Taxonomies', 'blocksy'),
										'clone' => 4,
										'options' => [

											'taxonomy' => [
												'label' => __( 'Taxonomy Source', 'blocksy' ),
												'type' => 'ct-select',
												'design' => 'block',
												'setting' => [ 'transport' => 'postMessage' ],
												'view' => 'text',
												'choices' => blocksy_ordered_keys($taxonomies_options),
												'value' => blocksy_maybe_get_matching_taxonomy('product'),
												'sync' => [
													'id' => 'woo_card_layout_meta'
												]
											],

											'style' => [
												'label' => __( 'Style', 'blocksy' ),
												'type' => 'ct-select',
												'value' => 'simple',
												'design' => 'block',
												'view' => 'text',
												'choices' => blocksy_ordered_keys(
													[
														'simple' => __( 'Default', 'blocksy' ),
														'pill' => __( 'Button', 'blocksy' ),
														'underline' => __( 'Underline', 'blocksy' ),
													]
												),
												'sync' => [
													'id' => 'woo_card_layout_meta'
												]
											],

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 10,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],

										],
									],

									'product_desc' => [
										'label' => __('Short Description', 'blocksy'),
										'options' => [
											'excerpt_source' => [
												'label' => __('Excerpt Type', 'blocksy'),
												'type' => 'ct-radio',
												'value' => 'excerpt',
												'view' => 'text',
												'choices' => [
													'excerpt' => __('Custom', 'blocksy'),
													'full' => __('Full', 'blocksy'),
												],
											],

											blocksy_rand_md5() => [
												'type' => 'ct-condition',
												'condition' => [ 'excerpt_source' => 'excerpt' ],
												'options' => [

													'excerpt_length' => [
														'label' => __('Length', 'blocksy'),
														'type' => 'ct-number',
														'design' => 'inline',
														'value' => 40,
														'min' => 1,
														'max' => 300,
													],

												],
											],

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 25,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],
										]
									],

									'product_add_to_cart' => [
										'label' => __('Add to Cart', 'blocksy'),
										'condition' => [
											'shop_cards_type' => 'type-1'
										],
										'options' => [

											'auto_hide_button' => [
												'label' => __( 'Auto Hide', 'blocksy' ),
												'type' => 'ct-switch',
												'value' => 'yes',
												'setting' => [ 'transport' => 'postMessage' ],
												'desc' => __( 'Automatically hide "Add to cart" button after adding the product to cart.', 'blocksy' ),
											],

											'button_equal_alignment' => [
												'label' => __( 'Equal Alignment', 'blocksy' ),
												'type' => 'ct-switch',
												'value' => 'yes',
												'setting' => [ 'transport' => 'postMessage' ],
												'desc' => __( 'Align all buttons on the same line, when positioned as the last element.', 'blocksy' ),
											],

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 0,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],

										],
									],

									'product_add_to_cart_and_price' => [
										'label' => __('Add to Cart and Price', 'blocksy'),
										'condition' => [
											'shop_cards_type' => 'type-2'
										],
										'options' => [

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 0,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],

										],
									],
								]
							),
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'shop_cards_type' => '!type-2' ],
							'options' => [

								'shop_cards_alignment' => [
									'type' => 'ct-radio',
									'label' => __( 'Content Alignment', 'blocksy' ),
									'view' => 'text',
									'design' => 'block',
									'divider' => 'bottom',
									'responsive' => true,
									'attr' => [ 'data-type' => 'alignment' ],
									'setting' => [ 'transport' => 'postMessage' ],
									'value' => 'CT_CSS_SKIP_RULE',
									'choices' => [
										'flex-start' => '',
										'center' => '',
										'flex-end' => '',
									],
								],
							],
						],

						'shopCardsGap' => [
							'label' => __( 'Columns Gap', 'blocksy' ),
							'type' => 'ct-slider',
							'value' => '30px',
							'units' => [
								['unit' => 'px', 'min' => 0, 'max' => 100],
								['unit' => 'em', 'min' => 0, 'max' => 100],
								['unit' => 'rem', 'min' => 0, 'max' => 100],
								['unit' => 'vw', 'min' => 0, 'max' => 100],
								['unit' => 'vh', 'min' => 0, 'max' => 100],
								['unit' => '', 'type' => 'custom'],
							],
							'responsive' => true,
							'setting' => [ 'transport' => 'postMessage' ],
						],

						'shopCardsRowGap' => [
							'label' => __( 'Rows Gap', 'blocksy' ),
							'type' => 'ct-slider',
							'value' => '30px',
							'units' => [
								['unit' => 'px', 'min' => 0, 'max' => 100],
								['unit' => 'em', 'min' => 0, 'max' => 100],
								['unit' => 'rem', 'min' => 0, 'max' => 100],
								['unit' => 'vw', 'min' => 0, 'max' => 100],
								['unit' => 'vh', 'min' => 0, 'max' => 100],
								['unit' => '', 'type' => 'custom'],
							],
							'responsive' => true,
							'setting' => [ 'transport' => 'postMessage' ],
						],
					],

					$card_additional_actions_options,

					apply_filters(
						'blocksy_woo_card_options_elements:after',
						[]
					)
				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					[
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_title:enabled' => '!no' ],
							'options' => [

								blocksy_rand_md5() => [
									'type' => 'ct-title',
									'label' => __( 'Title', 'blocksy' ),
								],

								'cardProductTitleFont' => [
									'type' => 'ct-typography',
									'label' => __( 'Font', 'blocksy' ),
									'value' => blocksy_typography_default_values([
										'size' => '17px',
										'variation' => 'n6',
									]),
									'setting' => [ 'transport' => 'postMessage' ],
								],

								'cardProductTitleColor' => [
									'label' => __( 'Font Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'hover' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => [
												'var(--theme-heading-1-color, var(--theme-headings-color))' => [
													'woo_card_layout:array-ids:product_title:heading_tag' => 'h1'
												],

												'var(--theme-heading-2-color, var(--theme-headings-color))' => [
													'woo_card_layout:array-ids:product_title:heading_tag' => 'h2'
												],

												'var(--theme-heading-3-color, var(--theme-headings-color))' => [
													'woo_card_layout:array-ids:product_title:heading_tag' => 'h3'
												],

												'var(--theme-heading-4-color, var(--theme-headings-color))' => [
													'woo_card_layout:array-ids:product_title:heading_tag' => 'h4'
												],

												'var(--theme-heading-5-color, var(--theme-headings-color))' => [
													'woo_card_layout:array-ids:product_title:heading_tag' => 'h5'
												],

												'var(--theme-heading-6-color, var(--theme-headings-color))' => [
													'woo_card_layout:array-ids:product_title:heading_tag' => 'h6'
												],

												'var(--theme-text-color)' => [
													'woo_card_layout:array-ids:product_title:heading_tag' => 'span|p'
												],
											]
										],

										[
											'title' => __( 'Hover', 'blocksy' ),
											'id' => 'hover',
											'inherit' => 'var(--theme-link-hover-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_desc:enabled' => '!no' ],
							'options' => [

								blocksy_rand_md5() => [
									'type' => 'ct-title',
									'label' => __( 'Short Description', 'blocksy' ),
								],

								'cardProductExcerptFont' => [
									'type' => 'ct-typography',
									'label' => __( 'Font', 'blocksy' ),
									'value' => blocksy_typography_default_values([]),
									'setting' => [ 'transport' => 'postMessage' ],
								],

								'cardProductExcerptColor' => [
									'label' => __( 'Font Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-text-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_price:enabled' => '!no' ],
							'options' => [

								blocksy_rand_md5() => [
									'type' => 'ct-title',
									'label' => __( 'Price', 'blocksy' ),
								],

								'cardProductPriceFont' => [
									'type' => 'ct-typography',
									'label' => __( 'Font', 'blocksy' ),
									'value' => blocksy_typography_default_values([
										'variation' => 'n6',
									]),
									'setting' => [ 'transport' => 'postMessage' ],
								],

								'cardProductPriceColor' => [
									'label' => __( 'Font Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-text-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_meta:enabled' => '!no' ],
							'options' => [

								blocksy_rand_md5() => [
									'type' => 'ct-title',
									'label' => __( 'Taxonomies', 'blocksy' ),
								],

								'card_product_categories_font' => [
									'type' => 'ct-typography',
									'label' => __( 'Font', 'blocksy' ),
									'sync' => 'live',
									'value' => blocksy_typography_default_values([
										'size' => [
											'desktop' => '12px',
											'tablet'  => '12px',
											'mobile'  => '12px'
										],
										'variation' => 'n6',
										'text-transform' => 'uppercase',
									]),
								],

								'cardProductCategoriesColor' => [
									'label' => __( 'Font Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => 'var(--theme-text-color)',
										],

										'hover' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
										],

										[
											'title' => __( 'Hover', 'blocksy' ),
											'id' => 'hover',
											'inherit' => 'var(--theme-link-hover-color)'
										],
									],
								],

								blocksy_rand_md5() => [
									'type' => 'ct-has-meta-category-button',
									'optionId' => 'woo_card_layout',
									'options' => [

										'card_product_categories_button_type_font_colors' => [
											'label' => __( 'Button Font Color', 'blocksy' ),
											'type'  => 'ct-color-picker',
											'design' => 'block:right',
											'divider' => 'top',
											'responsive' => true,
											'noColor' => [ 'background' => 'var(--theme-text-color)'],
											'sync' => 'live',
											'value' => [
												'default' => [
													'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
												],

												'hover' => [
													'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
												],
											],

											'pickers' => [
												[
													'title' => __( 'Initial', 'blocksy' ),
													'id' => 'default',
													'inherit' => 'var(--theme-button-text-initial-color)'
												],

												[
													'title' => __( 'Hover', 'blocksy' ),
													'id' => 'hover',
													'inherit' => 'var(--theme-button-text-hover-color)'
												],
											],
										],

										'card_product_categories_button_type_background_colors' => [
											'label' => __( 'Button Background', 'blocksy' ),
											'type'  => 'ct-color-picker',
											'design' => 'block:right',
											'responsive' => true,
											'noColor' => [ 'background' => 'var(--theme-text-color)'],
											'sync' => 'live',
											'value' => [
												'default' => [
													'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
												],

												'hover' => [
													'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
												],
											],

											'pickers' => [
												[
													'title' => __( 'Initial', 'blocksy' ),
													'id' => 'default',
													'inherit' => 'var(--theme-button-background-initial-color)'
												],

												[
													'title' => __( 'Hover', 'blocksy' ),
													'id' => 'hover',
													'inherit' => 'var(--theme-button-background-hover-color)'
												],
											],
										],

									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_sku:enabled' => '!no' ],
							'options' => [

								blocksy_rand_md5() => [
									'type' => 'ct-title',
									'label' => __( 'SKU', 'blocksy' ),
								],

								'cardProductSkuFont' => [
									'type' => 'ct-typography',
									'label' => __( 'Font', 'blocksy' ),
									'value' => blocksy_typography_default_values([]),
									'setting' => [ 'transport' => 'postMessage' ],
								],

								'cardProductSkuColor' => [
									'label' => __( 'Font Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-text-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'shop_cards_type' => 'type-1',
								'woo_card_layout:array-ids:product_add_to_cart:enabled' => '!no'
							],
							'options' => [

								blocksy_rand_md5() => [
									'type' => 'ct-title',
									'label' => __( 'Add to Cart Button', 'blocksy' ),
								],

								'cardProductButton1Text' => [
									'label' => __( 'Font Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'hover' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-button-text-initial-color)'
										],

										[
											'title' => __( 'Hover', 'blocksy' ),
											'id' => 'hover',
											'inherit' => 'var(--theme-button-text-hover-color)'
										],
									],
								],

								'cardProductButtonBackground' => [
									'label' => __( 'Background Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],
									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'hover' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-button-background-initial-color)'
										],

										[
											'title' => __( 'Hover', 'blocksy' ),
											'id' => 'hover',
											'inherit' => 'var(--theme-button-background-hover-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'shop_cards_type' => 'type-2',
								'woo_card_layout:array-ids:product_add_to_cart_and_price:enabled' => '!no'
							],
							'options' => [

								'cardProductButton2Text' => [
									'label' => __( 'Button Text Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'divider' => 'top:full',
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => 'var(--theme-text-color)',
										],

										'hover' => [
											'color' => 'var(--theme-link-hover-color)',
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
										],

										[
											'title' => __( 'Hover', 'blocksy' ),
											'id' => 'hover',
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'shop_cards_type' => 'type-2' ],
							'options' => [

								'cardProductBackground' => [
									'label' => __( 'Card Background Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'divider' => 'top:full',
									'setting' => [ 'transport' => 'postMessage' ],
									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-palette-color-8)'
										],
									],
								],

								'cardProductShadow' => [
									'label' => __( 'Card Shadow', 'blocksy' ),
									'type' => 'ct-box-shadow',
									'responsive' => true,
									'divider' => 'top',
									'setting' => [ 'transport' => 'postMessage' ],
									'value' => blocksy_box_shadow_value([
										'enable' => true,
										'h_offset' => 0,
										'v_offset' => 12,
										'blur' => 18,
										'spread' => -6,
										'inset' => false,
										'color' => [
											'color' => 'rgba(34, 56, 101, 0.03)',
										],
									])
								],

							],
						],
					],

					$card_additional_actions_design_options,

					[
						'cardProductRadius' => [
							'label' => [
								__('Image Border Radius', 'blocksy') => [
									'shop_cards_type' => 'type-1'
								],

								__('Card Border Radius', 'blocksy') => [
									'shop_cards_type' => 'type-2'
								]
							],
							'type' => 'ct-spacing',
							'divider' => 'top:full',
							'setting' => [ 'transport' => 'postMessage' ],
							'value' => blocksy_spacing_value([
								'top' => '3px',
								'left' => '3px',
								'right' => '3px',
								'bottom' => '3px',
							]),
							'responsive' => true
						],
					]
				],
			],

		],
	],
];

