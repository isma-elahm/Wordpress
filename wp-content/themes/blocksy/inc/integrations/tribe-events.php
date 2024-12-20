<?php

add_filter(
	'tribe_get_option_tribeEventsTemplate',
	function ($value) {
		return 'default';
	}
);

add_filter(
	'tec_events_display_settings_tab_fields',
	function ($fields) {
		if (isset($fields['tribeEventsTemplate'])) {
			$fields['tribeEventsTemplate']['conditional'] = false;
		}

		return $fields;
	}
);

add_filter(
	'tribe_events_views_v2_view_html_classes',
	function ($classes) {
		return array_filter($classes, function ($class) {
			return $class !== 'alignwide';
		});
	},
	50
);

add_filter(
	'blocksy:editor:post_types_for_rest_field',
	function ($post_types) {
		$post_types[] = 'tribe_events';

		return $post_types;
	}
);

add_filter(
	'blocksy:editor:post_meta_options',
	function ($options, $post_type) {
		if ($post_type !== 'tribe_events') {
			return $options;
		}

		return blocksy_get_options('integrations/the-events-calendar/meta');
	},
	10,
	2
);
