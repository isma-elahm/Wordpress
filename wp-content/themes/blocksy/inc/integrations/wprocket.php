<?php

// Never wrap ct_localizations in window.addEventListener('DOMContentLoaded', function() { ... });
// https://docs.wp-rocket.me/article/976-exclude-files-from-defer-js#exclude-inline-scripts

add_filter(
	'rocket_defer_inline_exclusions',
	function($inline_exclusions_list) {
		if (! is_array($inline_exclusions_list)) {
			$inline_exclusions_list = [];
		}

		$inline_exclusions_list[] = 'ct_localizations';

		return $inline_exclusions_list;
	}
);

add_filter('rocket_lrc_exclusions', function($exclusions) {
	$exclusions[] = 'class="ct-drawer-canvas" data-location="end"';
	return $exclusions;
});

