<?php

namespace Blocksy;

class DemoInstall {
	public function __construct() {
		$demo_export = new DemoInstallExport();

		$ajax_actions = [
			[
				'id' => 'blocksy_demo_export',
				'handler' => [$demo_export, 'request' ]
			],

			[
				'id' => 'blocksy_demo_get_export_data',
				'handler' => [$demo_export, 'get_export_data']
			],

			[
				'id' => 'blocksy_demo_get_content_preliminary_data',
				'handler' => [$this, 'demo_get_content_preliminary_data']
			],

			[
				'id' => 'blocksy_demo_install_child_theme',
				'handler' => [new DemoInstallChildThemeInstaller(), 'import']
			],

			[
				'id' => 'blocksy_demo_activate_plugins',
				'handler' => [new DemoInstallPluginsInstaller(), 'import']
			],

			[
				'id' => 'blocksy_demo_erase_content',
				'handler' => [new DemoInstallContentEraser(), 'import']
			],

			[
				'id' => 'blocksy_demo_install_widgets',
				'handler' => [new DemoInstallWidgetsInstaller(), 'import']
			],

			[
				'id' => 'blocksy_demo_install_options',
				'handler' => [new DemoInstallOptionsInstaller(), 'import']
			],

			[
				'id' => 'blocksy_demo_install_content',
				'handler' => [new DemoInstallContentInstaller(), 'import']
			],

			[
				'id' => 'blocksy_demo_register_current_demo',
				'handler' => [new DemoInstallRegisterDemo(), 'register']
			],

			[
				'id' => 'blocksy_demo_deregister_current_demo',
				'handler' => [new DemoInstallRegisterDemo(), 'deregister']
			],

			[
				'id' => 'blocksy_demo_deactivate_plugins',
				'handler' => [new DemoInstallPluginsUninstaller(), 'import']
			],

			[
				'id' => 'blocksy_demo_install_finish',
				'handler' => [new DemoInstallFinalActions(), 'import']
			],
		];

		foreach ($ajax_actions as $action) {
			add_action(
				'wp_ajax_' . $action['id'],
				function () use ($action) {
					$this->check_nonce();

					call_user_func($action['handler']);
				}
			);
		}

		add_filter(
			'blocksy_dashboard_localizations',
			function ($d) {
				$d['has_demo_install'] = apply_filters(
					'blocksy_ext_demo_install_enabled',
					'yes'
				);

				$d['demo_install_error'] = null;

				if (! extension_loaded('xml') && ! extension_loaded('simplexml')) {
					$d['demo_install_error'] = __("Your PHP installation doesn't have support for XML. Please install the <i>xml</i> or <i>simplexml</i> PHP extension in order to be able to install starter sites. You might need to contact your hosting provider to assist you in doing so.", 'blocksy-companion');
				}

				$d['current_installed_demo'] = $this->get_current_demo();

				$plugins = [
					'gutenberg' => false,
					'stackable-ultimate-gutenberg-blocks' => false,
					'wpforms-lite' => false,
					'woocommerce' => false,
					'elementor' => false,
					'brizy' => false,
					'getwid' => false,
					'simply-gallery-block' => false,
					'recipe-card-blocks-by-wpzoom' => false,
					'map-block-gutenberg' => false,
					'mb-custom-post-type' => false,
					'leadin' => false,
					'block-slider' => false,
					'ht-slider-for-elementor' => false,
					'modula-best-grid-gallery' => false,
					'advanced-custom-fields' => false,
					'greenshift-animation-and-page-builder-blocks' => false,
					'fluentform' => false,
					'translatepress-multilingual' => false,
					'fluent-booking' => false
				];

				foreach ($plugins as $plugin_name => $status) {
					$plugins_manager = $this->get_plugins_manager();

					$path = $plugins_manager->is_plugin_installed($plugin_name);

					if ($path) {
						if ($plugins_manager->is_plugin_active($path)) {
							$plugins[$plugin_name] = true;
						}
					}
				}

				$d['active_plugins'] = $plugins;

				return $d;
			}
		);
	}

	public function get_demo_remote_url($args = []) {
		$endpoint = 'https://startersites.io/';
		// $endpoint = 'https://demo.creativethemes.com/';
		// $endpoint = 'http://localhost:3008/';
		return $endpoint . '?' . http_build_query($args);
	}

	public function demo_get_content_preliminary_data() {
		if (! isset($_REQUEST['demo_name']) || !$_REQUEST['demo_name']) {
			wp_send_json_error([
				'message' => __("No demo name provided.", 'blocksy-companion')
			]);
		}

		$demo_name = explode(':', $_REQUEST['demo_name']);

		if (! isset($demo_name[1])) {
			$demo_name[1] = '';
		}

		$demo = $demo_name[0];
		$builder = $demo_name[1];

		$demo_content = Plugin::instance()->demo->fetch_single_demo([
			'demo' => $demo,
			'builder' => $builder,
			'field' => 'all'
		]);

		update_option('blocksy_ext_demos_currently_installing_demo', [
			'demo' => json_encode($demo_content)
		]);

		wp_send_json_success($demo_content);
	}

	public function get_current_demo() {
		return get_option('blocksy_ext_demos_current_demo', null);
	}

	public function blocksy_demo_get_export_data() {
		$this->check_nonce();

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

	public function get_plugins_manager() {
		if (! class_exists('Blocksy_Plugin_Manager')) {
			require_once get_template_directory() . '/admin/dashboard/plugins/ct-plugin-manager.php';
		}

		return new \Blocksy_Plugin_Manager();
	}

	public function fetch_single_demo($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'demo' => $args['demo'],
				'builder' => '',
				'field' => ''
			]
		);

		$body = blc_request_remote_url(
			$this->get_demo_remote_url([
				'route' => 'get_single',
				'demo' => $args['demo'] . ':' . $args['builder'],
				'field' => $args['field']
			]),

			[
				'user_agent_type' => 'wp'
			]
		);

		$body = json_decode($body, true);

		if (! $body) {
			return false;
		}

		return $body;
	}

	public function fetch_all_demos() {
		$body = blc_request_remote_url(
			$this->get_demo_remote_url([
				'route' => 'get_all'
			]),

			[
				'user_agent_type' => 'wp'
			]
		);

		if (is_wp_error($body)) {
			return $body;
		}

		if (! $body) {
			return new \WP_Error('demo_fetch_failed', 'Failed to fetch demos.');
		}

		$body = json_decode($body, true);

		if (! $body) {
			return false;
		}

		$data = get_plugin_data(BLOCKSY__FILE__);

		$result = [];

		foreach ($body as $single_demo) {
			if (! isset($single_demo['required_companion_version'])) {
				$result[] = $single_demo;
				continue;
			}

			if (version_compare(
				$data['Version'],
				$single_demo['required_companion_version'],
				'>='
			)) {
				$result[] = $single_demo;
			}
		}

		return $result;
	}

	public function check_nonce() {
		if (! check_ajax_referer('ct-dashboard', 'nonce', false)) {
			wp_send_json_error('nonce');
		}
	}

	public function get_currently_installing_demo() {
		$demo_to_install = get_option(
			'blocksy_ext_demos_currently_installing_demo',
			[]
		);

		if (! empty($demo_to_install) && ! empty($demo_to_install['demo'])) {
			$demo_to_install['demo'] = json_decode(
				$demo_to_install['demo'],
				true
			);
		}

		return $demo_to_install;
	}
}
