<?php

namespace Blocksy\DbVersioning;

class V2073 {
	public function migrate() {
		$old_render_layout_config = blocksy_get_theme_mod('product_compare_layout', [
			[
				'id' => 'product_main',
				'enabled' => true,
			],
			[
				'id' => 'product_price',
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
			[
				'id' => 'product_add_to_cart',
				'enabled' => true,
			],
		]);

		$missing_rows = [
			[
				'id' => 'product_title',
				'enabled' => true,
			]
		];

		$index_of_product_title = array_search(
			'product_title',
			array_column($old_render_layout_config, 'id')
		);


		if ($index_of_product_title !== false) {
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
