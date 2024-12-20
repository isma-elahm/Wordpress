<?php

namespace Blocksy\DbVersioning;

class V2075 {
	public function migrate() {
		if (! class_exists('Elementor\Plugin')) {
			$options_to_remove = [
				'elementor_disable_color_schemes',
				'elementor_disable_typography_schemes',
				'elementor_viewport_lg',
				'elementor_viewport_md'
			];

			foreach ($options_to_remove as $option) {
				if (get_option($option, '__DEFAULT__') !== '__DEFAULT__') {
					delete_option($option);
				}
			}
		}
	}
}
