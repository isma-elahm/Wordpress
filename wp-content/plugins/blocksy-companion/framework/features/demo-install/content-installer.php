<?php

namespace Blocksy;

class DemoInstallContentInstaller {
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
		add_filter(
			'option_uploads_use_yearmonth_folders',
			'__return_true',
			100
		);

		if (class_exists('\Astra_Sites')) {
			$astra_sites_instance = \Astra_Sites::get_instance();
			$elementor_integration = \AstraSites\Elementor\Astra_Sites_Compatibility_Elementor::get_instance();

			remove_filter(
				'wp_import_post_data_processed',
				[$astra_sites_instance, 'wp_slash_after_xml_import'],
				99, 2
			);

			if (
				defined('ELEMENTOR_VERSION')
				&&
				version_compare(ELEMENTOR_VERSION, '3.0.0', '>=')
			) {
				add_filter(
					'wp_import_post_meta',
					array('Elementor\Compatibility', 'on_wp_import_post_meta')
				);

				remove_filter(
					'wp_import_post_meta',
					array($elementor_integration, 'on_wp_import_post_meta')
				);
			}
		}

		if (
			! current_user_can('edit_theme_options')
			&&
			$this->is_ajax_request
		) {
			wp_send_json_error([
				'message' => __("Sorry, you don't have permission to install content.", 'blocksy-companion')
			]);
		}

		if (class_exists('\Elementor\Compatibility')) {
			remove_filter('wp_import_post_meta', [
				'\Elementor\Compatibility',
				'on_wp_import_post_meta'
			]);
		}

		add_action(
			'blocksy_wp_import_insert_term',
			function($term_id) {
				$this->track_term_insert($term_id);
			},
			10,
			1
		);

		add_action(
			'wp_import_insert_term',
			function($t) {
				$term_id = $t['term_id'];
				$this->track_term_insert($term_id);
			},
			10,
			1
		);

		add_filter(
			'wp_import_post_meta',
			function($meta, $post_id, $post) {
				$this->track_post_insert($post_id);

				$wp_importer = get_plugins('/wordpress-importer');

				if (! empty($wp_importer)) {
					$wp_importer_version = $wp_importer['wordpress-importer.php']['Version'];

					if (! empty($wp_importer_version)) {
						if (version_compare($wp_importer_version, '0.7', '>=')) {
							foreach ($meta as &$m) {
								if ('_elementor_data' === $m['key']) {
									$m['value'] = wp_slash($m['value']);
									break;
								}
							}
						}
					}
				} else {
					foreach ($meta as &$m) {
						if ('_elementor_data' === $m['key']) {
							$m['value'] = wp_slash($m['value']);
							break;
						}
					}
				}

				return $meta;
			},
			10,
			3
		);

		add_action(
			'wp_import_insert_post',
			function ($post_id, $old_post_id) {
				Plugin::instance()->header->patch_conditions(
					intval($post_id),
					intval($old_post_id)
				);
			},
			10, 2
		);

		add_filter(
			'wp_import_term_meta',
			function($meta, $term_id, $post) {
				$this->track_term_insert($term_id);

				return $meta;
			},
			10,
			3
		);

		if (! $this->demo_name) {
			if ($this->is_ajax_request) {
				wp_send_json_error([
					'message' => __("No demo name provided.", 'blocksy-companion')
				]);
			} else {
				return new \WP_Error(
					'blocksy_demo_install_content_no_demo_name',
					__("No demo name provided.", 'blocksy-companion')
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
			! isset($demo_to_install['demo']['content'])
		) {
			if ($this->is_ajax_request) {
				wp_send_json_error([
					'message' => __("No demo data found.", 'blocksy-companion'),
					'demo' => $demo_to_install
				]);
			} else {
				return new \WP_Error(
					'blocksy_demo_install_content_no_demo_data',
					__("No demo data found.", 'blocksy-companion')
				);
			}
		}

		$demo_to_install = $demo_to_install['demo'];

		$wp_import = new \Blocksy_WP_Import();

		$import_data = $wp_import->parse($demo_to_install['content']);

		$wp_import->get_authors_from_import($import_data);

		unset($import_data);

		$author_data = [];

		foreach ($wp_import->authors as $wxr_author) {
			$author = new \stdClass();

			// Always in the WXR
			$author->user_login = $wxr_author['author_login'];

			// Should be in the WXR; no guarantees
			if (isset($wxr_author['author_email'])) {
				$author->user_email = $wxr_author['author_email'];
			}

			if (isset($wxr_author['author_display_name'])) {
				$author->display_name = $wxr_author['author_display_name'];
			}

			if (isset($wxr_author['author_first_name'])) {
				$author->first_name = $wxr_author['author_first_name'];
			}

			if (isset($wxr_author['author_last_name'])) {
				$author->last_name = $wxr_author['author_last_name'];
			}

			$author_data[] = $author;
		}

		// Build the author mapping
		$author_mapping = $this->process_author_mapping( 'skip', $author_data );

		$author_in = wp_list_pluck($author_mapping, 'old_user_login');
		$author_out = wp_list_pluck($author_mapping, 'new_user_login');
		unset($author_mapping, $author_data);

		// $user_select needs to be an array of user IDs
		$user_select = [];
		$invalid_user_select = [];

		foreach ($author_out as $author_login) {
			$user = get_user_by('login', $author_login);

			if ($user) {
				$user_select[] = $user->ID;
			} else {
				$invalid_user_select[] = $author_login;
			}
		}

		if (! empty($invalid_user_select)) {
			# return new WP_Error( 'invalid-author-mapping', sprintf( 'These user_logins are invalid: %s', implode( ',', $invalid_user_select ) ) );
		}

		// Enable default GD library.
		add_filter('wp_image_editors', function ($editors) {
			$gd_editor = 'WP_Image_Editor_GD';
			$editors = array_diff($editors, array($gd_editor));
			array_unshift($editors, $gd_editor);
			return $editors;
		});

		unset($author_out);

		$wp_import->fetch_attachments = true;

		$_GET['import'] = 'wordpress';
		$_GET['step'] = 2;

		$_POST['imported_authors'] = $author_in;
		$_POST['user_map'] = $user_select;
		$_POST['fetch_attachments'] = $wp_import->fetch_attachments;

		ob_start();
		$wp_import->import($demo_to_install['content']);
		ob_end_clean();

		if (class_exists('Blocksy_Customizer_Builder')) {
			$header_builder = new \Blocksy_Customizer_Builder();
			$header_builder->patch_header_value_for($wp_import->processed_terms);
		}

		$old_nav_menu_locations = blocksy_get_theme_mod('nav_menu_locations', []);
		$should_update_nav_menu_locations = false;

		foreach ($old_nav_menu_locations as $location => $menu_id) {
			if (isset($wp_import->processed_terms[$menu_id])) {
				$should_update_nav_menu_locations = true;

				$old_nav_menu_locations[
					$location
				] = $wp_import->processed_terms[$menu_id];
			}
		}

		if ($should_update_nav_menu_locations) {
			set_theme_mod('nav_menu_locations', $old_nav_menu_locations);
		}

		$this->clean_plugins_cache();
		$this->assign_pages_ids($demo, $builder);

		if ($this->is_ajax_request) {
			wp_send_json_success([
				'processed_posts' => $wp_import->processed_posts,
			]);
		}
	}

	public function track_post_insert($post_id) {
		update_post_meta($post_id, 'blocksy_demos_imported_post', true);
	}

	public function track_term_insert($term_id) {
		update_term_meta($term_id, 'blocksy_demos_imported_term', true);
	}

	public function clean_plugins_cache() {
		if (class_exists('\Elementor\Plugin')) {
			\Elementor\Plugin::$instance->posts_css_manager->clear_cache();
		}

		if (is_callable('FLBuilderModel::delete_asset_cache_for_all_posts')) {
			\FLBuilderModel::delete_asset_cache_for_all_posts();
		}
	}

	public function assign_pages_ids($demo, $builder) {
		$demo_content = Plugin::instance()->demo->fetch_single_demo([
			'demo' => $demo,
			'builder' => $builder,
			'field' => 'content'
		]);

		if (! isset($demo_content['pages_ids_options'])) {
			if ($this->is_ajax_request) {
				wp_send_json_error([
					'message' => __("No pages to assign.", 'blocksy-companion')
				]);
			} else {
				return new \WP_Error(
					'blocksy_demo_install_content_no_pages',
					__("No pages to assign.", 'blocksy-companion')
				);
			}
		}

		foreach ($demo_content['pages_ids_options'] as $option_id => $page_title) {
			if (strpos($option_id, 'woocommerce') !== false) {
				if (! class_exists('WooCommerce')) {
					continue;
				}
			}

			$page = get_page_by_title($page_title);

			if (isset($page) && $page->ID) {
				update_option($option_id, $page->ID);
			}
		}
	}

	private function process_author_mapping($authors_arg, $author_data) {
		switch ($authors_arg) {
		// Create authors if they don't yet exist; maybe match on email or user_login
		case 'create':
			return $this->create_authors_for_mapping($author_data);
		// Skip any sort of author mapping
		case 'skip':
			return array();
		default:
			return new WP_Error( 'invalid-argument', "'authors' argument is invalid." );
		}
	}

	private function create_authors_for_mapping($author_data) {
		$author_mapping = [];

		foreach ($author_data as $author) {
			if (isset($author->user_email)) {
				$user = get_user_by('email', $author->user_email);

				if ($user instanceof WP_User) {
					$author_mapping[] = [
						'old_user_login' => $author->user_login,
						'new_user_login' => $user->user_login,
					];

					continue;
				}
			}

			$user = get_user_by('login', $author->user_login);

			if ($user instanceof WP_User) {
				$author_mapping[] = [
					'old_user_login' => $author->user_login,
					'new_user_login' => $user->user_login,
				];
				continue;
			}

			$user = array(
				'user_login' => '',
				'user_email' => '',
				'user_pass' => wp_generate_password(),
			);

			$user = array_merge($user, (array) $author);
			$user_id = wp_insert_user($user);

			if (is_wp_error($user_id)) {
				return $user_id;
			}

			$user = get_user_by( 'id', $user_id );
			$author_mapping[] = [
				'old_user_login' => $author->user_login,
				'new_user_login' => $user->user_login,
			];
		}

		return $author_mapping;
	}
}

