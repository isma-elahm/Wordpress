<?php

namespace Blocksy;

class Dashboard {
	public function __construct() {
		add_filter(
			'blocksy:dashboard:redirect-after-activation',
			function ($url) {
				return add_query_arg(
					'page',
					'ct-dashboard',
					admin_url('admin.php')
				);
			}
		);

		add_filter(
			'blocksy_add_menu_page',
			function ($res, $options) {
				add_menu_page(
					$options['title'],
					$options['menu-title'],
					$options['permision'],
					$options['top-level-handle'],
					$options['callback'],
					$options['icon-url'],
					2
				);

				add_submenu_page(
					$options['top-level-handle'],
					$options['title'],
					__('Dashboard', 'blocksy-companion'),
					$options['permision'],
					$options['top-level-handle']
				);

				return true;
			},
			10, 2
		);

		add_action(
			'admin_menu',
			[$this, 'setup_framework_page'],
			5
		);

		add_filter(
			'blocksy:dashboard:redirect-after-activation',
			function ($url) {
				return add_query_arg(
					'page',
					'ct-dashboard',
					admin_url('admin.php')
				);
			}
		);

		add_action('admin_notices', function () {
			$blocksy_data = Plugin::instance()->is_blocksy_data;

			if (
				! Plugin::instance()->check_if_blocksy_is_activated()
				&&
				$blocksy_data
				&&
				$blocksy_data['is_correct_theme']
			) {
				echo blocksy_render_view(
					dirname(__FILE__) . '/views/theme-mismatch.php',
					[
						'is_theme_version_ok' => $blocksy_data['is_theme_version_ok'],
						'is_companion_version_ok' => $blocksy_data['is_companion_version_ok'],
					]
				);
			}
		});

		add_action(
			'admin_menu',
			function () {
				if (Plugin::instance()->check_if_blocksy_is_activated()) {
					return;
				}

				$menu_slug = plugin_basename('ct-dashboard');
				$hookname = get_plugin_page_hookname('ct-dashboard', '');
				remove_all_actions($hookname);

				add_action(
					$hookname,
					function () {
						$this->welcome_page_template();
					}
				);
			},
			99999999999
		);

		add_action(
			'admin_enqueue_scripts',
			[$this, 'enqueue_static'],
			100
		);

		add_action('admin_body_class', function ($class) {
			if (! Plugin::instance()->check_if_blocksy_is_activated()) {
				return $class;
			}

			if (function_exists('blc_fs') && blc_fs()->is_activation_mode()) {
				$class .= ' blocksy-fs-optin-dashboard';
			}

			return $class;
		});

		if (function_exists('blc_fs')) {
			blc_fs()->add_filter('hide_plan_change', '__return_true');
			blc_fs()->add_filter(
				'plugin_icon',
				function ($url) {
					return BLOCKSY_PATH . '/static/img/logo.jpg';
				}
			);

			blc_fs()->add_filter(
				'permission_diagnostic_default',
				'__return_false'
			);

			blc_fs()->add_filter(
				'show_deactivation_feedback_form',
				'__return_false'
			);

			blc_fs()->add_filter('hide_freemius_powered_by', '__return_true');

			blc_fs()->add_filter( 'show_deactivation_subscription_cancellation', '__return_false' );

			blc_fs()->add_filter(
				'connect-message_on-premium',
				function ($text) {
					if (strpos($text, '<br>') !== false) {
						$exploded_message = explode('<br>', $text);

						$text = '<span>' . $exploded_message[0] . '</span>' . $exploded_message[1];
					}

					return $text;
				}
			);

			blc_fs()->add_filter(
				'connect_message_on_update',
				function (
					$message,
					$user_first_name,
					$product_title,
					$user_login,
					$site_link,
					$freemius_link
				) {
					$is_network_upgrade_mode = ( fs_is_network_admin() && blc_fs()->is_network_upgrade_mode() );
					$slug = blc_fs()->get_slug();
					$is_gdpr_required = \FS_GDPR_Manager::instance()->is_required();
					$hey_x_text = esc_html( blc_safe_sprintf( fs_text_x_inline( 'Hey %s,', 'greeting', 'hey-x', $slug ), $user_first_name ) );

					$default_optin_message = $is_gdpr_required ?
						fs_text_inline( 'Never miss an important update - opt in to our security & feature updates notifications, educational content, offers, and non-sensitive diagnostic tracking with %4$s. If you skip this, that\'s okay! %1$s will still work just fine.', 'connect-message_on-update', $slug ) :
						fs_text_inline( 'Never miss an important update - opt in to our security & feature updates notifications, and non-sensitive diagnostic tracking with %4$s. If you skip this, that\'s okay! %1$s will still work just fine.', 'connect-message_on-update', $slug );

					$default_optin_message = 'Never miss an important update - opt in to our security & feature updates notifications, educational content, offers, and non-sensitive diagnostic tracking with.';

					return (($is_network_upgrade_mode ?
						'' :
						/* translators: %s: name (e.g. Hey John,) */
						'<span>' . $hey_x_text . '</span>'
					) .
					blc_safe_sprintf(
						esc_html( $default_optin_message ),
						'<b>' . esc_html( blc_fs()->get_plugin_name() ) . '</b>',
						'<b>' . $user_login . '</b>',
						$site_link,
						$freemius_link
					));

				}, 10, 6
			);

			blc_fs()->add_action('connect/before', function () {
				$path = dirname(__FILE__) . '/views/optin.php';

				echo blocksy_render_view(
					$path,
					[]
				);
			});

			blc_fs()->add_action('connect/after', function () {
				echo '</div>';
			});

			add_action(
				'wp_ajax_blocksy_fs_connect_again',
				function () {
					if (! current_user_can('edit_theme_options')) {
						wp_send_json_error();
					}

					blc_fs()->connect_again();
					wp_send_json_success();
				}
			);

			add_action(
				'wp_ajax_blocksy_dashboard_handle_incorrect_license',
				function () {
					if (! current_user_can(
						blc_get_capabilities()->get_wp_capability_by('dashboard')
					)) {
						wp_send_json_error();
					}

					$blocksy_active_extensions_old = get_option(
						'blocksy_active_extensions_old',
						'__empty__'
					);

					if ($blocksy_active_extensions_old !== '__empty__') {
						return;
					}

					$activated_extensions = get_option('blocksy_active_extensions', []);

					update_option(
						'blocksy_active_extensions_old',
						$activated_extensions
					);

					delete_option('blocksy_active_extensions');

					wp_send_json_success();
				}
			);
		}

		add_filter(
			'blocksy_dashboard_localizations',
			function ($d) {
				$plugin_data = get_plugin_data(BLOCKSY__FILE__);

				$result = [
					'is_pro' => false,
					'is_anonymous' => false,
					'connect_template' => '',
					'retrieve_demos_data' => [],
					'plugin_version' => $plugin_data['Version']
				];

				if (function_exists('blc_fs')) {
					$is_anonymous = blc_fs()->is_anonymous();
					$connect_template = '';

					if ($is_anonymous) {
						ob_start();
						blc_fs()->_connect_page_render();
						$connect_template = ob_get_clean();
					}

					$current_plan = blc_get_capabilities()->get_plan();

					// $current_plan = 'free';

					$retrieve_demos_data = [
					];

					if (blc_fs()->_get_license()) {
						$retrieve_demos_data['license_id'] = blc_fs()->_get_license()->id;
					}

					if (blc_fs()->get_site()) {
						$retrieve_demos_data['install_id'] = blc_fs()->get_site()->id;
					}

					$result = [
						'is_pro' => $current_plan !== 'free',
						'current_plan' => $current_plan,

						'pro_starter_sites' => blc_get_capabilities()->get_features()['pro_starter_sites'],
						'pro_starter_sites_enhanced' => blc_get_capabilities()->get_features()['pro_starter_sites_enhanced'],

						'is_anonymous' => $is_anonymous,
						'connect_template' => $connect_template,
						'retrieve_demos_data' => $retrieve_demos_data,
						'plugin_version' => $plugin_data['Version']
					];
				}

				if (
					Plugin::instance()->premium
					&&
					is_callable([
						Plugin::instance()->premium,
						'user_wants_beta_updates'
					])
				) {
					$result['has_beta_consent'] = Plugin::instance()->premium->user_wants_beta_updates();
				}

				if (function_exists('blocksy_get_pricing_links')) {
					$result['modal_links'] = blocksy_get_pricing_links();
				}

				return array_merge($result, $d);
			}
		);

		add_action('admin_init', function ($plugin) {
			if (wp_doing_ajax()) {
				return;
			}

			if (! is_admin()) {
				return;
			}

			if (! is_user_logged_in()) {
				return;
			}

			if (is_network_admin()) {
				return;
			}

			if (intval(get_option('blc_activation_redirect', false)) === wp_get_current_user()->ID) {
				delete_option('blc_activation_redirect');
				exit(wp_redirect(admin_url('admin.php?page=ct-dashboard')));
			}
		});
	}

	public function enqueue_static() {
		if (! $this->is_dashboard_page()) {
			$this->enqueue_static_global();
			return;
		}

		$deps = apply_filters('blocksy-dashboard-scripts-dependencies', [
			'wp-i18n',
			'ct-events',
			'ct-options-scripts'
		]);

		if (Plugin::instance()->check_if_blocksy_is_activated()) {
			wp_enqueue_script(
				'blocksy-dashboard-scripts',
				BLOCKSY_URL . 'static/bundle/dashboard.js',
				$deps,
				blc_get_version(),
				false
			);
		} else {
			wp_enqueue_script(
				'blocksy-dashboard-scripts',
				BLOCKSY_URL . 'static/bundle/dashboard-no-theme.js',
				[
					'underscore',
					'react',
					'react-dom',
					'wp-element',
					'wp-date',
					'wp-i18n',
					'wp-util'
				],
				blc_get_version(),
				false
			);

			$slug = 'blocksy';

			$localize_data = [
				'themeIsInstalled' => (
					!! wp_get_theme($slug)
					&&
					! wp_get_theme($slug)->errors()
				),
				'updatesNonce' => wp_installing() ? '' : wp_create_nonce('updates'),
				'activate'=> current_user_can('switch_themes') ? wp_nonce_url(admin_url('themes.php?action=activate&amp;stylesheet=' . $slug), 'switch-theme_' . $slug) : null
			];

			$blocksy_data = Plugin::instance()->is_blocksy_data;

			if ($blocksy_data && $blocksy_data['is_correct_theme']) {
				$mismatched_product_name = 'Blocksy theme';
				$mismatched_product_slug = 'blocksy';

				if (
					$blocksy_data['is_theme_version_ok']
					&&
					! $blocksy_data['is_companion_version_ok']
				) {
					$mismatched_product_name = 'Blocksy Companion plugin';
					$mismatched_product_slug = 'blocksy-companion';

					if (blc_can_use_premium_code()) {
						$mismatched_product_name = 'Blocksy Companion Pro plugin';
						$mismatched_product_slug = 'blocksy-companion-pro';
					}
				}

				$localize_data['theme_version_mismatch'] = [
					'productName' => $mismatched_product_name,
					'slug' => $mismatched_product_slug
				];
			}

			wp_localize_script(
				'blocksy-dashboard-scripts',
				'ctDashboardLocalizations',
				$localize_data
			);

			wp_dequeue_style('ct-dashboard-styles');
		}

		wp_enqueue_style(
			'blocksy-dashboard-styles',
			BLOCKSY_URL . 'static/bundle/dashboard.min.css',
			['wp-components'],
			blc_get_version()
		);
	}

	public function enqueue_static_global() {
		$slug = 'blocksy';

		$themeIsInstalled = (
			!! wp_get_theme($slug)
			&&
			! wp_get_theme($slug)->errors()
		);

		$blocksy_data = Plugin::instance()->is_blocksy_data;

		if (
			! Plugin::instance()->check_if_blocksy_is_activated()
			&&
			$blocksy_data
			&&
			$blocksy_data['is_correct_theme']
		) {
			wp_enqueue_style(
				'blocksy-dashboard-styles',
				BLOCKSY_URL . 'static/bundle/dashboard.min.css',
				[],
				blc_get_version()
			);

			wp_enqueue_script(
				'blocksy-admin-notifications-scripts',
				BLOCKSY_URL . 'static/bundle/notifications.js',
				[
					'underscore',
					'react',
					'react-dom',
					'wp-element',
					'wp-date',
					'wp-i18n'
				],
				blc_get_version(),
				false
			);

			wp_localize_script(
				'blocksy-admin-notifications-scripts',
				'ctDashboardLocalizations',
				[
					'updatesNonce' => wp_installing() ? '' : wp_create_nonce('updates'),
				]
			);
		}
	}

	public function setup_framework_page() {
		if (! current_user_can(blc_get_capabilities()->get_wp_capability_by('dashboard'))) {
			return;
		}

		$options = [
			'title' => __('Blocksy', 'blocksy-companion'),
			'menu-title' => __('Blocksy', 'blocksy-companion'),
			'permision' => blc_get_capabilities()->get_wp_capability_by('dashboard'),
			'top-level-handle' => 'ct-dashboard',
			'callback' => [$this, 'welcome_page_template'],
			'icon-url' => apply_filters(
				'blocksy:dashboard:icon-url',
				'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKCSB2aWV3Qm94PSIwIDAgMzUgMzUiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDM1IDM1OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxwYXRoIGQ9Ik0yMS42LDIxLjNjMCwwLjYtMC41LDEuMS0xLjEsMS4xaC0zLjVsLTAuOS0yLjJoNC40QzIxLjEsMjAuMiwyMS42LDIwLjcsMjEuNiwyMS4zeiBNMjAuNiwxMy41aC00LjRsMC45LDIuMmgzLjUKCWMwLjYsMCwxLjEtMC41LDEuMS0xLjFDMjEuNiwxNCwyMS4xLDEzLjUsMjAuNiwxMy41eiBNMzUsMTcuNUMzNSwyNy4yLDI3LjIsMzUsMTcuNSwzNUM3LjgsMzUsMCwyNy4yLDAsMTcuNUMwLDcuOCw3LjgsMCwxNy41LDAKCUMyNy4yLDAsMzUsNy44LDM1LDE3LjV6IE0yNSwxNy45YzAuNy0wLjksMS4xLTIuMSwxLjEtMy40YzAtMS4yLTAuNC0yLjQtMS4xLTMuM2MtMS0xLjQtMi42LTIuMy00LjQtMi4zYzAsMC0wLjEsMC0wLjEsMHYwSDkuOQoJYy0wLjMsMC0wLjUsMC4zLTAuNCwwLjVsMi42LDYuMkg5LjljLTAuMywwLTAuNSwwLjMtMC40LDAuNUwxNCwyNi45aDYuNWMzLjEsMCw1LjYtMi41LDUuNi01LjZDMjYuMiwyMCwyNS44LDE4LjksMjUsMTcuOQoJQzI1LjEsMTcuOSwyNS4xLDE3LjksMjUsMTcuOXoiLz4KPC9zdmc+Cg=='
			),
			'position' => 2,
		];

		add_menu_page(
			$options['title'],
			$options['menu-title'],
			$options['permision'],
			$options['top-level-handle'],
			$options['callback'],
			$options['icon-url'],
			2
		);
	}

	public function is_dashboard_page() {
		global $pagenow;

		if (is_network_admin()) {
			$is_ct_settings =
				// 'themes.php' === $pagenow &&
				isset( $_GET['page'] ) && 'blocksy-companion' === $_GET['page'];

			return $is_ct_settings;
		}

		$is_ct_settings =
			// 'themes.php' === $pagenow &&
			isset( $_GET['page'] ) && 'ct-dashboard' === $_GET['page'];

		return $is_ct_settings;
	}

	public function welcome_page_template() {
		if (! current_user_can(blc_get_capabilities()->get_wp_capability_by('dashboard'))) {
			wp_die(
				esc_html(
					__( 'You do not have sufficient permissions to access this page.', 'blocksy-companion' )
				)
			);
		}

		echo '<div id="ct-dashboard"></div>';
	}
}

if (! function_exists('blocksy_render_view')) {
	function blocksy_render_view(
		$file_path,
		$view_variables = [],
		$default_value = ''
	) {
		if (! is_file($file_path)) {
			return $default_value;
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract($view_variables, EXTR_REFS);
		unset($view_variables);

		ob_start();
		require $file_path;

		return ob_get_clean();
	}
}
