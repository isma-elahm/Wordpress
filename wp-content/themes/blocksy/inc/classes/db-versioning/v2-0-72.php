<?php

namespace Blocksy\DbVersioning;

class V2072 {
	public function migrate() {
		$cleaner = new DefaultValuesCleaner();
		$cleaner->clean_whole_customizer_recursively();

		$default_enabled = [
			'facebook',
			'twitter',
			'pinterest',
			'linkedin',
		];

		$old_share_options = [
			'reddit',
			'hacker_news',
			'vk',
			'ok',
			'telegram',
			'viber',
			'whatsapp',
			'flipboard',
			'line',
			'threads',
			'email',
			'clipboard',
		];

		$old_share_options = array_merge($default_enabled, $old_share_options);

		$default_value_for_new_option = [];

		foreach ($default_enabled as $option) {
			$default_value_for_new_option[] = [
				'id' => $option,
				'enabled' => true
			];
		}

		$prefixes = [];

		foreach (blocksy_manager()->screen->get_single_prefixes() as $prefix) {
			$prefixes[] = $prefix;
		}

		foreach ($prefixes as $prefix) {
			$new_option_value = [];

			foreach ($old_share_options as $old_share_option) {
				$option_key = $prefix . '_share_' . $old_share_option;

				$old_value = get_theme_mod($option_key, '__empty__');

				if ($old_value === '__empty__') {
					if (in_array($old_share_option, $default_enabled)) {
						$new_option_value[] = [
							'id' => $old_share_option,
							'enabled' => true
						];
					}

					continue;
				}

				remove_theme_mod($option_key);

				if ($old_value === 'no') {
					continue;
				}

				$new_option_value[] = [
					'id' => $old_share_option,
					'enabled' => true
				];
			}

			if ($new_option_value != $default_value_for_new_option) {
				set_theme_mod(
					$prefix . '_' . 'share_networks',
					$new_option_value
				);
			}
		}
	}

	public function migrate_compare_table_layers() {
		$old_render_layout_config = blocksy_get_theme_mod('product_compare_layout', [
			[
				'id' => 'product_main',
				'enabled' => true,
			],
			[
				'id' => 'product_description',
				'enabled' => true,
			],
			[
				'id' => 'product_attributes',
				'enabled' => true,
				'product_attributes_source' => 'all',
			],
			[
				'id' => 'product_availability',
				'enabled' => true,
			],
		]);

		$missing_rows = [
			[
				'id' => 'product_price',
				'enabled' => true,
			],
			[
				'id' => 'product_add_to_cart',
				'enabled' => true,
			],
		];

		$index_of_product_price = array_search(
			'product_price',
			array_column($old_render_layout_config, 'id')
		);

		$index_of_product_add_to_cart = array_search(
			'product_add_to_cart',
			array_column($old_render_layout_config, 'id')
		);

		if (
			$index_of_product_price !== false
			||
			$index_of_product_add_to_cart !== false
		) {
			return;
		}

		$index_of_product_main = array_search(
			'product_main',
			array_column($old_render_layout_config, 'id')
		);

		if ($index_of_product_main !== false) {
			array_splice(
				$old_render_layout_config,
				$index_of_product_main + 1,
				0,
				$missing_rows
			);
		}

		set_theme_mod('product_compare_layout', $old_render_layout_config);
	}
}
