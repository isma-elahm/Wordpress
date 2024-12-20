<?php

$options = [];

$custom_rules = apply_filters('blocksy:conditions:rules:custom', []);

$result = [];

foreach ($custom_rules as $rule) {
	if (! isset($rule['id']) || ! isset($rule['title'])) {
		continue;
	}

	$new_rule = [
		'id' => $rule['id'],
		'title' => $rule['title'],
		'is_custom_rule' => true
	];

	if (isset($rule['has_text_field'])) {
		$new_rule['has_text_field'] = $rule['has_text_field'];
	}

	if (isset($rule['choices'])) {
		$new_rule['choices'] = $rule['choices'];
	}

	$result[] = $new_rule;
}

if (! empty($result)) {
	$options[] = [
		'title' => __('Custom', 'blocksy-companion'),
		'rules' => $result
	];
}



