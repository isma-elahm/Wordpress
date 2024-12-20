<?php

// Function to get proper default value for a responsive value.
// [
//   'desktop' => 'value',
//   'tablet' => 'value',
//   'mobile' => 'value',
// ] => 'value'
//
// 'value' => 'value'
//
// [
//   'desktop' => 'value',
//   'tablet' => 'value',
//   'mobile' => 'value1',
// ] => [
//   'desktop' => 'value',
//   'tablet' => 'value',
//   'mobile' => 'value1',
// ]
function blocksy_default_responsive_value($value) {
	if (
		// If is just plain array
		is_array($value) && ! isset($value['desktop'])
		&&
		// If it's scalar value
		! is_array($value)
	) {
		return $value;
	}

	if (
		isset($value['desktop'])
		&&
		isset($value['tablet'])
		&&
		isset($value['mobile'])
	) {
		if (
			$value['desktop'] == $value['tablet']
			&&
			$value['desktop'] == $value['mobile']
		) {
			return $value['desktop'];
		}
	}

	return $value;
}

if (! function_exists('blocksy_expand_responsive_value')) {
	function blocksy_expand_responsive_value($value, $is_responsive = true) {
		if (is_array($value) && isset($value['desktop'])) {
			if (! $is_responsive) {
				return $value['desktop'];
			}

			return $value;
		}

		if (! $is_responsive) {
			return $value;
		}

		return [
			'desktop' => $value,
			'tablet' => $value,
			'mobile' => $value,
		];
	}
}

if (! function_exists('blocksy_map_values')) {
	function blocksy_map_values($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'value' => null,
				'map' => []
			]
		);

		if (
			! is_array($args['value'])
			&&
			isset($args['map'][$args['value']])
		) {
			return $args['map'][$args['value']];
		}

		if (! is_array($args['value'])) {
			return $args['value'];
		}

		foreach ($args['value'] as $key => $value) {
			if (! is_array($value) && isset($args['map'][$value])) {
				$args['value'][$key] = $args['map'][$value];
			}
		}

		return $args['value'];
	}
}

function blocksy_output_css_vars($args = []) {
	$args = wp_parse_args(
		$args,
		[
			'css' => null,
			'tablet_css' => null,
			'mobile_css' => null,

			// string or array
			// array is used for responsive selector
			'selector' => null,

			'desktop_selector_prefix' => '',
			'tablet_selector_prefix' => '',
			'mobile_selector_prefix' => '',

			'variableName' => null,

			// custom-property | property
			'variableType' => 'custom-property',

			'value' => null,

			'value_suffix' => '',

			'responsive' => false
		]
	);

	if (! $args['variableName']) {
		throw new Error('variableName missing in args!');
	}

	if ($args['responsive']) {
		blocksy_assert_args($args, ['tablet_css', 'mobile_css']);
	}

	$value = blocksy_expand_responsive_value($args['value']);

	if ($args['variableType'] === 'custom-property') {
		$args['variableName'] = '--' . $args['variableName'];
	}

	if (
		substr_count(
			$value['desktop'], '('
		) !== substr_count(
			$value['desktop'], ')'
		)
	) {
		$value['desktop'] = '"' . $value['desktop'] . '"';
	}

	if (
		substr_count(
			$value['tablet'], '('
		) !== substr_count(
			$value['tablet'], ')'
		)
	) {
		$value['tablet'] = '"' . $value['tablet'] . '"';
	}

	if (
		substr_count(
			$value['mobile'], '('
		) !== substr_count(
			$value['mobile'], ')'
		)
	) {
		$value['mobile'] = '"' . $value['mobile'] . '"';
	}

	$responsive_selector = blocksy_expand_responsive_value($args['selector']);

	if (! empty($args['desktop_selector_prefix'])) {
		$responsive_selector['desktop'] = implode(' ', [
			$args['desktop_selector_prefix'],
			$responsive_selector['desktop']
		]);
	}

	if (! empty($args['tablet_selector_prefix'])) {
		$responsive_selector['tablet'] = implode(' ', [
			$args['tablet_selector_prefix'],
			$responsive_selector['tablet']
		]);
	}

	if (! empty($args['mobile_selector_prefix'])) {
		$responsive_selector['mobile'] = implode(' ', [
			$args['mobile_selector_prefix'],
			$responsive_selector['mobile']
		]);
	}

	$args['css']->put(
		$responsive_selector['desktop'],
		$args['variableName'] . ': ' . $value['desktop'] . $args['value_suffix']
	);

	if (
		$args['responsive']
		&&
		(
			$value['tablet'] !== $value['desktop']
			||
			$responsive_selector['desktop'] !== $responsive_selector['tablet']
		)
	) {
		$args['tablet_css']->put(
			$responsive_selector['tablet'],
			$args['variableName'] . ': ' . $value['tablet'] . $args['value_suffix']
		);
	}

	if (
		$args['responsive']
		&&
		(
			$value['tablet'] !== $value['mobile']
			||
			$responsive_selector['tablet'] !== $responsive_selector['mobile']
		)
	) {
		$args['mobile_css']->put(
			$responsive_selector['mobile'],
			$args['variableName'] . ': ' . $value['mobile'] . $args['value_suffix']
		);
	}
}
