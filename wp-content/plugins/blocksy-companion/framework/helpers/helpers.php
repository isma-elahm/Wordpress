<?php

function blc_get_version() {
	if (! function_exists('get_plugin_data')) {
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}

	// Prevent early translation call by setting $translate to false.
	$plugin_data = get_plugin_data(
		BLOCKSY__FILE__,
		false,
		/* $translate */ false
	);

	return $plugin_data['Version'];
}

function blc_get_capabilities() {
	static $capabilities = null;

	if ($capabilities === null) {
		$capabilities = new Blocksy\Capabilities();
	}

	return $capabilities;
}

function blc_can_use_premium_code() {
	return !! class_exists('Blocksy\Premium');
}

function blc_site_has_feature($feature = 'base_pro') {
	return (
		blc_can_use_premium_code()
		&&
		blc_get_capabilities()->has_feature($feature)
	);
}

// https://developer.wordpress.org/reference/functions/is_ssl/
function blc_maybe_is_ssl() {
	// cloudflare
	if (! empty($_SERVER['HTTP_CF_VISITOR'])) {
		$cfo = json_decode($_SERVER['HTTP_CF_VISITOR']);

		if (isset($cfo->scheme) && 'https' === $cfo->scheme) {
			return true;
		}
	}

	// other proxy
	if (
		! empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
		&&
		'https' === $_SERVER['HTTP_X_FORWARDED_PROTO']
	) {
		return true;
	}

	// is_ssl() sometimes returns false when it should return true,
	// for example updates.
	if (strpos(strtolower(get_site_url()), 'https://') === 0) {
		return true;
	}

	return function_exists('is_ssl') ? is_ssl() : false;
}

// Don't use protocol relative URL, it's an anti pattern.
// https://www.paulirish.com/2010/the-protocol-relative-url/
function blc_normalize_site_url($url) {
	$parsed_url = parse_url($url);

	$protocol = 'http';

	if (blc_maybe_is_ssl()) {
		$protocol .= 's';
	}

	$result = $protocol . '://' . $parsed_url['host'];

	if (isset($parsed_url['port'])) {
		$result = $result . ':' . $parsed_url['port'];
	}

	if (isset($parsed_url['path'])) {
		$result = $result . $parsed_url['path'];
	}

	return $result;
}

if (! function_exists('blc_load_xml_file')) {
	function blc_load_xml_file($url, $args = []) {
		$args = wp_parse_args($args, [
			'user_agent' => ''
		]);

		set_time_limit(300);

		if (ini_get('allow_url_fopen') && ini_get('allow_url_fopen') !== 'Off') {
			$context_options = [
				"ssl" => [
					"verify_peer" => false,
					"verify_peer_name" => false,
				]
			];

			if (! empty($args['user_agent'])) {
				$context_options['http'] = [
					'user_agent' => $args['user_agent']
				];
			}

			return file_get_contents(
				$url,
				false,
				stream_context_create($context_options)
			);
		} else if (function_exists('curl_init')) {
			$curl = curl_init($url);

			if (! empty($args['user_agent'])) {
				curl_setopt($curl, CURLOPT_USERAGENT, $args['user_agent']);
			}

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

			$result = curl_exec($curl);
			curl_close($curl);

			return $result;
		} else {
			throw new Exception("Can't load data.");
		}
	}
}

function blc_stringify_url($parsed_url) {
	$scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
	$host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
	$port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
	$user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
	$pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
	$pass = ($user || $pass) ? "$pass@" : '';
	$path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
	$query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
	$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

	return "$scheme$user$pass$host$port$path$query$fragment";
}

function blc_is_xhr() {
	return (
		isset($_REQUEST['blocksy_ajax'])
		&&
		strtolower($_REQUEST['blocksy_ajax']) === 'yes'
	);
}

function blc_get_option_from_db($option, $default = '') {
	try {
		global $wpdb;

		$suppress = $wpdb->suppress_errors();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1",
				$option
			)
		);

		$wpdb->suppress_errors($suppress);

		if (is_object($row)) {
			return maybe_unserialize($row->option_value);
		}
	} catch (Exception $e) {
	}

	return $default;
}

function blc_get_network_option_from_db($network_id, $option, $default = '') {
	if ($network_id && ! is_numeric($network_id)) {
		return false;
	}

	$network_id = (int) $network_id;

	// Fallback to the current network if a network ID is not specified.
	if (! $network_id) {
		$network_id = get_current_network_id();
	}

	try {
		global $wpdb;

		$suppress = $wpdb->suppress_errors();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = %s AND site_id = %d",
				$option,
				$network_id
			)
		);

		$wpdb->suppress_errors($suppress);

		if (is_object($row)) {
			return maybe_unserialize($row->meta_value);
		}
	} catch (Exception $e) {
	}

	return $default;
}

function blc_safe_sprintf($format, ...$args) {
	$result = $format;

	$is_error = false;

	// vsprintf() triggers a warning on PHP < 8 and throws an exception on PHP 8+
	// We need to handle both.
	// https://www.php.net/manual/en/function.vsprintf.php#refsect1-function.vsprintf-errors

	set_error_handler(function () use (&$is_error) {
		$is_error = true;
	});

	if (interface_exists('Throwable')) {
		try {
			$result = vsprintf($format, $args);
		} catch (\Throwable $e) {
			$is_error = true;
		}
	} else {
		$result = vsprintf($format, $args);
	}

	restore_error_handler();

	if ($is_error) {
		// TODO: maybe cleanup format from %s, %d, etc
		return $format;
	}

	return $result;
}

function blc_get_terms($get_terms_args, $args = []) {
	$args = wp_parse_args($args, [
		'all_languages' => false
	]);

	if (! $args['all_languages']) {
		return get_terms($get_terms_args);
	}

	global $sitepress;

	if (function_exists('PLL')) {
		remove_filter(
			'terms_clauses',
			[PLL()->terms, 'terms_clauses'],
			10, 3
		);
	}

	if ($sitepress) {
		remove_filter('get_terms_args', array($sitepress, 'get_terms_args_filter'), 10, 2);
		remove_filter('get_term', array($sitepress, 'get_term_adjust_id'), 1, 1);
		remove_filter('terms_clauses', array($sitepress, 'terms_clauses'), 10, 3);

		$all_terms = get_terms($get_terms_args);

		add_filter('terms_clauses', array($sitepress, 'terms_clauses'), 10, 3);
		add_filter('get_term', array($sitepress, 'get_term_adjust_id'), 1, 1);
		add_filter('get_terms_args', array($sitepress, 'get_terms_args_filter' ), 10, 2);
	} else {
		$all_terms = get_terms($get_terms_args);
	}

	if (function_exists('PLL')) {
		add_filter(
			'terms_clauses',
			[PLL()->terms, 'terms_clauses'],
			10, 3
		);
	}

	return $all_terms;
}

function blc_request_remote_url($url, $args = []) {
	$request = new \Blocksy\RequestRemoteUrl();
	return $request->request($url, $args);
}

function blc_get_jed_locale_data($domain) {
	static $locale = [];

	if (isset($locale[$domain])) {
		return $locale[$domain];
	}

	$translations = get_translations_for_domain($domain);

	$locale[$domain] = [
		'' => [
			'domain' => $domain,
			'lang' => get_user_locale(),
		]
	];

	if (! empty($translations->headers['Plural-Forms'])) {
		$locale[$domain]['']['plural_forms'] = $translations->headers['Plural-Forms'];
	}

	foreach ($translations->entries as $msgid => $entry) {
		$locale[$domain][$entry->key()] = $entry->translations;
	}

	foreach (blc_get_json_translation_files('blocksy-companion') as $file_path) {
		$parsed_json = json_decode(
			call_user_func(
				'file' . '_get_contents',
				$file_path
			),
			true
		);

		if (
			! $parsed_json
			||
			! isset($parsed_json['locale_data']['messages'])
		) {
			continue;
		}

		foreach ($parsed_json['locale_data']['messages'] as $msgid => $entry) {
			if (empty($msgid)) {
				continue;
			}

			$locale[$domain][$msgid] = $entry;
		}
	}

	return $locale[$domain];
}

function blc_get_variables_from_file(
	$file_path,
	array $_extract_variables,
	array $_set_variables = array()
) {
	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	extract($_set_variables, EXTR_REFS);
	unset($_set_variables);

	if (is_file($file_path)) {
		require $file_path;
	}

	foreach ($_extract_variables as $variable_name => $default_value) {
		if (isset($$variable_name) ) {
			$_extract_variables[$variable_name] = $$variable_name;
		}
	}

	return $_extract_variables;
}

function blc_get_json_translation_files($domain) {
	$cached_mofiles = [];

	$locations = [
		WP_LANG_DIR . '/themes',
		WP_LANG_DIR . '/plugins'
	];

	foreach ($locations as $location) {
		$mofiles = glob($location . '/*.json');

		if (! $mofiles) {
			continue;
		}

		$cached_mofiles = array_merge($cached_mofiles, $mofiles);
	}

	$locale = determine_locale();

	$result = [];

	foreach ($cached_mofiles as $single_file) {
		if (strpos($single_file, $locale) === false) {
			continue;
		}

		$result[] = $single_file;
	}

	return $result;
}
