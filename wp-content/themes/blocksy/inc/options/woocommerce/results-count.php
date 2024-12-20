<?php

$options = [
	'has_shop_results_count' => [
		'label' => __( 'Results Count', 'blocksy' ),
		'type' => 'ct-panel',
		'switch' => true,
		'value' => 'yes',
		'sync' => blocksy_sync_whole_page([
			'prefix' => 'woo_categories',
			'loader_selector' => '.woo-listing-top'
		]),
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'shop_results_count_visibility' => [
						'label' => __('Visibility', 'blocksy'),
						'type' => 'ct-visibility',
						'design' => 'block',
						'divider' => 'top',
						'setting' => ['transport' => 'postMessage'],
						'allow_empty' => true,
						'value' => blocksy_default_responsive_value([
							'desktop' => true,
							'tablet' => true,
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

					'shop_results_count_font' => [
						'type' => 'ct-typography',
						'label' => __( 'Font', 'blocksy' ),
						'value' => blocksy_typography_default_values([
							'size' => '11px',
							'variation' => 'n5',
							'letter-spacing' => '0.05em',
							'text-transform' => 'uppercase'
						]),
						'setting' => [ 'transport' => 'postMessage' ],
					],

					'shop_results_count_color' => [
						'label' => __( 'Font Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'divider' => 'bottom',
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

		],
	],
];