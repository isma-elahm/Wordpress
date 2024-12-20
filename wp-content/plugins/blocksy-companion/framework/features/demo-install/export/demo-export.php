<?php

namespace Blocksy;

class DemoInstallExport {
	public function request() {
		if (! current_user_can('edit_theme_options')) {
			wp_send_json_error();
		}

		global $wp_customize;

		$demoId = sanitize_text_field($_REQUEST['demoId']);
		$builder = sanitize_text_field($_REQUEST['builder']);
		$plugins = sanitize_text_field($_REQUEST['plugins']);

		$plugins = explode(',', preg_replace('/\s+/', '', $plugins));

		$options_data = new DemoInstallOptionsExport();

		$widgets_data = new DemoInstallWidgetsExport();
		$widgets_data = $widgets_data->export();

		add_filter(
			'export_wp_all_post_types',
			function ($post_types) {
				$post_types['wpforms'] = 'wpforms';
				return $post_types;
			}
		);

		$content_data = new DemoInstallContentExport();
		$content_data = $content_data->export();

		$demo_data = [
			'options' => $options_data->export(),
			'widgets' => $widgets_data,
			'content' => $content_data,

			'pages_ids_options' => $options_data->export_pages_ids_options(),
			'created_at' => date('d-m-Y'),

			'builder' => $builder,
			'plugins' => $plugins
		];

		update_option( 'blocksy_ext_demos_exported_demo_data', [
			'demoId' => $demoId,
			'builder' => $builder,
			'plugins' => $plugins
		]);

		wp_send_json_success([
			'demo' => $demo_data
		]);
	}

	public function get_export_data() {
		if (! current_user_can('edit_theme_options')) {
			wp_send_json_error();
		}

		$data = get_option(
			'blocksy_ext_demos_exported_demo_data',
			[]
		);

		wp_send_json_success([
			'data' => $data
		]);
	}
}
