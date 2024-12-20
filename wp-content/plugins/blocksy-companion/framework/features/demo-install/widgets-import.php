<?php

namespace Blocksy;

class DemoInstallWidgetsInstaller {
	protected $demo_name = null;
	protected $is_ajax_request = true;

	public function __construct($args = []) {
		$args = wp_parse_args($args, [
			'demo_name' => null,
			'is_ajax_request' => true,
		]);

		if (
			!$args['demo_name']
			&&
			isset($_REQUEST['demo_name'])
			&&
			$_REQUEST['demo_name']
		) {
			$args['demo_name'] = $_REQUEST['demo_name'];
		}

		$this->demo_name = $args['demo_name'];
		$this->is_ajax_request = $args['is_ajax_request'];
	}

	public function import() {
		if (
			! current_user_can('edit_theme_options')
			&&
			$this->is_ajax_request
		) {
			wp_send_json_error([
				'message' => __("Sorry, you don't have permission to install widgets.", 'blocksy-companion')
			]);
		}

		if (! $this->demo_name) {
			if ($this->is_ajax_request) {
				wp_send_json_error([
					'message' => __("No demo to install", 'blocksy-companion')
				]);
			} else {
				return new \WP_Error(
					'blocksy_demo_install_widgets_no_demo',
					__("No demo to install", 'blocksy-companion')
				);
			}
		}

		$demo_name = explode(':', $this->demo_name);

		if (! isset($demo_name[1])) {
			$demo_name[1] = '';
		}

		$demo = $demo_name[0];
		$builder = $demo_name[1];

		$demo_to_install = Plugin::instance()->demo->get_currently_installing_demo();

		if (
			empty($demo_to_install)
			||
			! isset($demo_to_install['demo'])
			||
			! isset($demo_to_install['demo']['widgets'])
		) {
			if ($this->is_ajax_request) {
				wp_send_json_error([
					'message' => __("No widgets to install.", 'blocksy-companion'),
					'demo' => $demo_to_install
				]);
			} else {
				return new \WP_Error(
					'blocksy_demo_install_widgets_no_widgets',
					__("No widgets to install.", 'blocksy-companion')
				);
			}
		}

		$data = json_decode(json_encode($demo_to_install['demo']['widgets']));

		$result = $this->import_data($data);

		if ($this->is_ajax_request) {
			wp_send_json_success();
		}
	}

	public function import_data($data) {
		if (empty($data)) {
			return false;
		}

		if (! is_object($data) && ! is_array($data)) {
			return false;
		}

		$sidebars_widgets = get_option('sidebars_widgets');

		if (! $sidebars_widgets) {
			$sidebars_widgets = [];
		}

		foreach ($data as $sidebar_id => $widgets) {
			if ('wp_inactive_widgets' === $sidebar_id) {
				continue;
			}

			$result = $this->handle_single_sidebar($sidebar_id, $widgets);

			foreach ($result as $widget_id) {
				$sidebars_widgets[$sidebar_id][] = $widget_id;
			}
		}

		update_option('sidebars_widgets', $sidebars_widgets);
		unset($sidebars_widgets['array_version']);
		set_theme_mod('sidebars_widgets', [
			'time' => time(),
			'data' => $sidebars_widgets
		]);
	}

	private function handle_single_sidebar($sidebar_id, $widgets) {
		global $wp_registered_sidebars;

		$available_widgets = $this->available_widgets();

		$widget_instances = [];

		foreach ($available_widgets as $widget_data) {
			$widget_instances[$widget_data['id_base']] = get_option(
				'widget_' . $widget_data['id_base']
			);
		}

		if (! isset($wp_registered_sidebars[$sidebar_id])) {
			$sidebar_id = 'wp_inactive_widgets';
		}

		$result = [];

		foreach ($widgets as $widget_instance_id => $widget) {
			$id_base = preg_replace('/-[0-9]+$/', '', $widget_instance_id);
			$instance_id_number = str_replace($id_base . '-', '', $widget_instance_id);

			// Does site support this widget?
			if (! isset($available_widgets[$id_base])) {
				continue;
			}

			// Convert multidimensional objects to multidimensional arrays
			// Some plugins like Jetpack Widget Visibility store settings
			// as multidimensional arrays Without this, they are imported as
			// objects and cause fatal error on Widgets page If this creates
			// problems for plugins that do actually intend settings in
			// objects then may need to consider other
			// approach: https://wordpress.org/support/topic/problem-with-array-of-arrays
			// It is probably much more likely that arrays are used than objects, however.
			$widget = json_decode(wp_json_encode($widget), true);

			/**
			 * Does widget with identical settings already exist in same sidebar?
			 * Skip identical widget.
			 */
			/*
			if (isset($widget_instances[$id_base])) {
				// Get existing widgets in this sidebar.
				$sidebar_widgets = isset(
					$sidebars_widgets[$sidebar_id]
				) ? $sidebars_widgets[$sidebar_id] : [];

				// Loop widgets with ID base.
				$single_widget_instances = ! empty(
					$widget_instances[$id_base]
				) ? $widget_instances[$id_base] : [];

				$is_in_same_sidebar_with_same_id = false;

				foreach ($single_widget_instances as $check_id => $check_widget) {
					// Is widget in same sidebar and has identical settings?
					if (
						in_array("$id_base-$check_id", $sidebar_widgets, true)
						&&
						(array) $widget === $check_widget
					) {
						$is_in_same_sidebar_with_same_id = true;
					}
				}

				if ($is_in_same_sidebar_with_same_id) {
					continue;
				}
			}
			 */

			// Add widget instance
			// All instances for that widget ID base, get fresh every time.
			global $wpdb;

			$single_widget_instances = [];

			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1",
					'widget_' . $id_base
				)
			);

			if (! is_null($row)) {
				$single_widget_instances = maybe_unserialize($row->option_value);
			}

			if (empty($single_widget_instances)) {
				$single_widget_instances = [
					'_multiwidget' => 1,
				];
			}

			$new_instance_id_number = 1;

			while (true) {
				if (! isset($single_widget_instances[$new_instance_id_number])) {
					break;
				}

				$new_instance_id_number++;
			}

			// Add it.
			$single_widget_instances[$new_instance_id_number] = $widget;

			if (isset($single_widget_instances['_multiwidget'])) {
				$multiwidget = $single_widget_instances['_multiwidget'];
				unset($single_widget_instances['_multiwidget']);
				$single_widget_instances['_multiwidget'] = $multiwidget;
			}

			update_option('widget_' . $id_base, $single_widget_instances, false);

			$new_instance_id = $id_base . '-' . $new_instance_id_number;

			$result[] = $new_instance_id;
		}

		return $result;
	}

	private function available_widgets() {
		global $wp_registered_widget_controls;

		$widget_controls = $wp_registered_widget_controls;
		$available_widgets = [];

		foreach ($widget_controls as $widget) {
			if (
				!empty($widget['id_base'])
				&&
				!isset($available_widgets[$widget['id_base']])
			) {
				$available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
				$available_widgets[$widget['id_base']]['name'] = $widget['name'];
			}
		}

		return $available_widgets;
	}
}

