<?php

namespace Blocksy\DbVersioning;

class DefaultValuesCleaner {
	public function clean_whole_customizer() {
		$all_mods = get_theme_mods();

		$ignored_mods = [
			'header_placements',
			'footer_placements'
		];

		foreach ($all_mods as $mod_name => $mod_value) {
			if (in_array($mod_name, $ignored_mods)) {
				continue;
			}

			if (isset($mod_value['desktop'])) {
				$cleaned_value = $this->clean_responsive_value($mod_value);

				if ($cleaned_value['changed']) {
					set_theme_mod($mod_name, $cleaned_value['value']);
				}
			}
		}

		blocksy_manager()->db->wipe_cache();
	}

	public function clean_whole_customizer_recursively() {
		$all_mods = get_theme_mods();

		$theme_slug = get_option('stylesheet');

		$option_name = "theme_mods_$theme_slug";

		$mods = get_option($option_name, '__empty__');

		if ($mods === '__empty__' || ! is_array($mods)) {
			return;
		}

		$new_mods = [];

		$change_count = 0;

		foreach ($mods as $mod_name => $mod_value) {
			$cleaned_value = $this->clean_value_recursively($mod_value);

			if ($cleaned_value['changed']) {
				$new_mods[$mod_name] = $cleaned_value['value'];
				$change_count++;
			} else {
				$new_mods[$mod_name] = $mod_value;
			}
		}

		if ($change_count > 0) {
			update_option($option_name, $new_mods);
		}
	}

	public function clean_value_recursively($value) {
		if (! is_array($value)) {
			return [
				'value' => $value,
				'changed' => false
			];
		}

		if ($this->is_responsive_value($value)) {
			return $this->clean_responsive_value($value);
		}

		$result = [];

		$changed = false;

		foreach ($value as $key => $mod_value) {
			if ($this->is_responsive_value($mod_value)) {
				$clean_result = $this->clean_responsive_value($mod_value);

				if ($clean_result['changed']) {
					$changed = true;
				}

				$result[$key] = $clean_result['value'];

				continue;
			}

			$clean_result = $this->clean_value_recursively($mod_value);

			$result[$key] = $clean_result['value'];

			if ($clean_result['changed']) {
				$changed = true;
			}
		}

		return [
			'value' => $result,
			'changed' => $changed
		];
	}

	public function clean_header() {
		$header_placements = blocksy_get_theme_mod(
			'header_placements',
			'__empty__'
		);

		if ($header_placements === '__empty__') {
			return;
		}

		$has_changed = false;

		$new_header_placements = $header_placements;

		foreach ($header_placements['sections'] as $section_index => $section) {
			if (empty($section['items'])) {
				continue;
			}

			foreach ($section['items'] as $item_index => $item) {
				if (empty($item['id']) || empty($item['values'])) {
					continue;
				}

				$cleaned_item_payload = $this->cleanup_item($item);

				if (! $cleaned_item_payload['item_changed']) {
					continue;
				}

				$has_changed = true;

				$new_header_placements['sections'][$section_index]['items'][
					$item_index
				]['values'] = $cleaned_item_payload['new_item'];
			}
		}

		if ($has_changed) {
			set_theme_mod('header_placements', $new_header_placements);
		}
	}

	public function clean_footer() {
		$footer_placements = blocksy_get_theme_mod(
			'footer_placements',
			'__empty__'
		);

		if ($footer_placements === '__empty__') {
			return;
		}

		$has_changed = false;

		$new_footer_placements = $footer_placements;

		foreach ($footer_placements['sections'] as $section_index => $section) {
			if (empty($section['items'])) {
				continue;
			}

			foreach ($section['items'] as $item_index => $item) {
				if (empty($item['id']) || empty($item['values'])) {
					continue;
				}

				$cleaned_item_payload = $this->cleanup_item($item, 'footer');

				if (! $cleaned_item_payload['item_changed']) {
					continue;
				}

				$has_changed = true;

				$new_footer_placements['sections'][$section_index]['items'][
					$item_index
				]['values'] = $cleaned_item_payload['new_item'];
			}
		}

		if ($has_changed) {
			set_theme_mod('footer_placements', $new_footer_placements);
		}
	}

	// new_item = []
	// item_changed = false
	public function cleanup_item($item, $panel_type = 'header') {
		$item_changed = false;

		$registered_items = blocksy_manager()->builder->get_registered_items_by(
			$panel_type
		);

		$item_data = null;

		foreach ($registered_items as $registered_item) {
			if ($registered_item['id'] === $item['id']) {
				$item_data = $registered_item;
				break;
			}
		}

		if (! $item_data) {
			return [
				'item_changed' => false
			];
		}

		$options = blocksy_manager()->builder->get_options_for(
			$panel_type,
			$item_data
		);

		$collected = [];

		blocksy_collect_options(
			$collected,
			$options,
			[
				'limit_option_types' => false,
				'limit_level' => 0,
				'include_container_types' => false,
				'info_wrapper' => false,
			]
		);

		$new_item = [];

		foreach ($item['values'] as $item_key => $item_value) {
			if (! isset($collected[$item_key])) {
				continue;
			}

			if ($item_value != $collected[$item_key]['value']) {
				$clean_result = $this->clean_responsive_value($item_value);

				if ($clean_result['changed']) {
					$item_changed = true;
				}

				$new_item[$item_key] = $clean_result['value'];
			}
		}

		if (count($new_item) === 0) {
			return [
				'item_changed' => true,
				'new_item' => []
			];
		}

		return [
			'item_changed' => $item_changed,
			'new_item' => $new_item
		];
	}

	private function clean_responsive_value($value) {
		if (! is_array($value) || ! isset($value['desktop'])) {
			return [
				'value' => $value,
				'changed' => false
			];
		}

		$device_keys = [
			'desktop',
			'tablet',
			'mobile'
		];

		$allowed_keys = array_merge($device_keys, ['__changed']);

		$new_value = [];

		foreach ($allowed_keys as $device) {
			if (isset($value[$device])) {
				$new_value[$device] = $value[$device];
			}
		}

		$equal_to_all = false;

		if (isset($new_value['tablet']) && isset($new_value['mobile'])) {
			$equal_to_all = $this->deep_compare(
				$new_value['desktop'],
				$new_value['tablet']
			);

			$equal_to_all = $this->deep_compare(
				$new_value['desktop'],
				$new_value['mobile']
			);
		}

		if ($equal_to_all) {
			return [
				'value' => $new_value['desktop'],
				'changed' => true
			];
		}

		return [
			'value' => $new_value,
			'changed' => ! $this->deep_compare($new_value, $value)
		];
	}

	private function is_responsive_value($value) {
		return (
			isset($value['desktop'])
			&&
			// Header placements also has a `desktop` key but is not a
			// responsive value, so it should be skipped.
			(
				! isset($value['mode'])
				||
				$value['mode'] !== 'placements'
			)
		);
	}

	private function deep_compare($a, $b) {
		return $a == $b;
	}
}
