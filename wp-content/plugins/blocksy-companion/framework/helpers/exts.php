<?php

function blc_exts_get_preliminary_config($ext = null, $args = []) {
	$args = wp_parse_args($args, [
		'only_billing_data' => false
	]);

	$data = blc_get_variables_from_file(
		dirname(__FILE__) . '/exts-configs.php',
		[
			'data' => []
		],
		[
			'only_billing_data' => $args['only_billing_data']
		]
	);

	$data = $data['data'];

	if (! $ext || ! isset($data[$ext])) {
		return $data;
	}

	return $data[$ext];
}

function blc_get_ext($id, $args = []) {
	if (! \Blocksy\Plugin::instance()->extensions) {
		return null;
	}

	return \Blocksy\Plugin::instance()->extensions->get($id, $args);
}
