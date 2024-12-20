<?php

$options = [
	'woo_has_related_upsells' => [
		'label' => __( 'Related & Upsells', 'blocksy' ),
		'type' => 'ct-panel',
		'switch' => true,
		'value' => 'yes',
		'sync' => blocksy_sync_whole_page([
			'prefix' => 'product',
			'loader_selector' => '.type-product'
		]),
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					apply_filters(
						'blocksy_customizer_options:woocommerce:related:slider_general',
						[]
					),

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							'woocommerce_related_products_slideshow' => '!slider',
						],
						'options' => [

							'woo_product_related_cards_columns' => [
								'label' => __('Columns & Rows', 'blocksy'),
								'type' => 'ct-woocommerce-columns-and-rows',
								'value' => [
									'desktop' => 4,
									'tablet' => 3,
									'mobile' => 1,
									'__changed' => ['tablet', 'mobile']
								],
								'min' => 1,
								'max' => 6,
								'responsive' => true,
								'sync' => blocksy_sync_whole_page([
									'prefix' => 'product',
									'loader_selector' => '[class*="post"] .products'
								]),
								'columns_id' => 'woo_product_related_cards_columns',
								'rows_id' => 'woo_product_related_cards_rows',
								'columns_value' => [
									'desktop' => 4,
									'tablet' => 3,
									'mobile' => 1,
									'__changed' => ['tablet', 'mobile']
								],
								'rows_value' => 1,
							],

							'woo_product_related_cards_rows' => [
								'type' => 'hidden',
								'value' => 1,
								'sync' => blocksy_sync_whole_page([
									'prefix' => 'product',
									'loader_selector' => '[class*="post"] .products'
								]),
							],

							blocksy_rand_md5() => [
								'type' => 'ct-divider',
							],

						]
					],

					'woo_product_related_label_tag' => [
						'label' => __( 'Module Title Tag', 'blocksy' ),
						'type' => 'ct-select',
						'value' => 'h2',
						'view' => 'text',
						'design' => 'inline',
						'divider' => 'bottom:full',
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
						'sync' => blocksy_sync_whole_page([
							'prefix' => 'product',
							'loader_selector' => '[class*="post"] .products .ct-module-title'
						]),
					],

					'related_products_visibility' => [
						'label' => __('Related Products Visibility', 'blocksy'),
						'type' => 'ct-visibility',
						'design' => 'block',
						'setting' => ['transport' => 'postMessage'],
						'allow_empty' => true,
						'value' => blocksy_default_responsive_value([
							'desktop' => true,
							'tablet' => false,
							'mobile' => false,
						]),
						'choices' => blocksy_ordered_keys([
							'desktop' => __( 'Desktop', 'blocksy' ),
							'tablet' => __( 'Tablet', 'blocksy' ),
							'mobile' => __( 'Mobile', 'blocksy' ),
						]),
					],

					blocksy_rand_md5() => [
						'type' => 'ct-divider',
					],

					'upsell_products_visibility' => [
						'label' => __('Upsell Products Visibility', 'blocksy'),
						'type' => 'ct-visibility',
						'design' => 'block',
						'setting' => ['transport' => 'postMessage'],
						'allow_empty' => true,
						'value' => blocksy_default_responsive_value([
							'desktop' => true,
							'tablet' => false,
							'mobile' => false,
						]),
						'choices' => blocksy_ordered_keys([
							'desktop' => __( 'Desktop', 'blocksy' ),
							'tablet' => __( 'Tablet', 'blocksy' ),
							'mobile' => __( 'Mobile', 'blocksy' ),
						]),
					],

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					[
						'related_upsells_heading_font' => [
							'type' => 'ct-typography',
							'label' => __( 'Module Title Font', 'blocksy' ),
							'value' => blocksy_typography_default_values([
								'size' => '20px',
							]),
							'setting' => [ 'transport' => 'postMessage' ],
						],

						'related_upsells_heading_font_color' => [
							'label' => __( 'Module Title Color', 'blocksy' ),
							'type'  => 'ct-color-picker',
							'design' => 'inline',
							'sync' => 'live',
							'divider' => 'bottom',
							'value' => [
								'default' => [
									'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
								],
							],
							'pickers' => [
								[
									'title' => __( 'Initial', 'blocksy' ),
									'id' => 'default',
									'inherit' => [
										'var(--theme-heading-1-color, var(--theme-headings-color))' => [
											'woo_product_related_label_tag' => 'h1'
										],

										'var(--theme-heading-2-color, var(--theme-headings-color))' => [
											'woo_product_related_label_tag' => 'h2'
										],

										'var(--theme-heading-3-color, var(--theme-headings-color))' => [
											'woo_product_related_label_tag' => 'h3'
										],

										'var(--theme-heading-4-color, var(--theme-headings-color))' => [
											'woo_product_related_label_tag' => 'h4'
										],

										'var(--theme-heading-5-color, var(--theme-headings-color))' => [
											'woo_product_related_label_tag' => 'h5'
										],

										'var(--theme-heading-6-color, var(--theme-headings-color))' => [
											'woo_product_related_label_tag' => 'h6'
										],

										'var(--theme-text-color)' => [
											'woo_product_related_label_tag' => 'span|p'
										],
									]
								],
							],
						],
					],

					apply_filters(
						'blocksy_customizer_options:woocommerce:related:slider_design',
						[]
					),

				],
			],

		],
	],
];
