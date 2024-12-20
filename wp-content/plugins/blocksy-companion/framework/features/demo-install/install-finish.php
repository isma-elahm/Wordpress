<?php

namespace Blocksy;

class DemoInstallFinalActions {
	protected $is_ajax_request = true;

	public function __construct($args = []) {
		$args = wp_parse_args($args, [
			'is_ajax_request' => true,
		]);

		$this->is_ajax_request = $args['is_ajax_request'];
	}

	public function import() {
		if (
			! current_user_can('edit_theme_options')
			&&
			$this->is_ajax_request
		) {
			wp_send_json_error([
				'message' => __("Sorry, you don't have permission to finish the installation.", 'blocksy-companion')
			]);
		}

		delete_option('blocksy_ext_demos_currently_installing_demo');

		$wpforms_settings = get_option('wpforms_settings', []);
		$wpforms_settings['disable-css'] = '2';
		update_option('wpforms_settings', $wpforms_settings);

		$this->replace_urls();

		if (class_exists('\FluentForm\App\Hooks\Handlers\ActivationHandler')) {
			$fluentFormActivation = new \FluentForm\App\Hooks\Handlers\ActivationHandler();
			$fluentFormActivation->migrate();
		}

		do_action('customize_save_after');
		do_action('blocksy:dynamic-css:refresh-caches');
		do_action('blocksy:cache-manager:purge-all');

		if (class_exists('WC_REST_System_Status_Tools_V2_Controller')) {
			if (! defined('WP_CLI')) {
				define('WP_CLI', true);
			}

			$s = new \WC_REST_System_Status_Tools_V2_Controller();

			$s->execute_tool('clear_transients');
			if (function_exists('wc_update_product_lookup_tables')) {
				wc_update_product_lookup_tables();
			}
			$s->execute_tool('clear_transients');
		}

		$this->handle_brizy_posts();

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		if (get_option('qubely_global_options')) {
			$maybe_presets = json_decode(
				get_option('qubely_global_options'),
				true
			);

			if (
				$maybe_presets
				&&
				isset($maybe_presets['activePreset'])
				&&
				$maybe_presets['activePreset'] !== 'theme'
			) {
				$maybe_presets['activePreset'] = 'theme';

				update_option(
					'qubely_global_options',
					json_encode($maybe_presets)
				);
			}
		}

		$this->maybe_activate_elementor_experimental_container();

		$this->update_counts_for_all_terms();

		$this->patch_attachment_ids_in_mods();

		if ($this->is_ajax_request) {
			wp_send_json_success();
		}
	}

	/**
	 * Replace elementor URLs
	 */
	public function replace_urls() {
		$current_demo = Plugin::instance()->demo->get_current_demo();

		if (! $current_demo) {
			return;
		}

		if (! isset($current_demo['demo'])) {
			return;
		}

		$demo_name = explode(':', $current_demo['demo']);

		if (! isset($demo_name[1])) {
			$demo_name[1] = '';
		}

		$demo = $demo_name[0];
		$builder = $demo_name[1];

		$demo_content = Plugin::instance()->demo->fetch_single_demo([
			'demo' => $demo,
			'builder' => $builder
		]);

		if (! $demo_content) {
			return;
		}

		if (! isset($demo_content['url'])) {
			return;
		}

		$from = $demo_content['url'];

		$to = get_site_url();

		$wp_uploads = wp_upload_dir();

		if (isset($wp_uploads['baseurl'])) {
			$from .= '/wp-content/uploads';
			$to = $wp_uploads['baseurl'];
		}

		$from = trim($from);
		$to = trim($to);

		if (
			! filter_var($from, FILTER_VALIDATE_URL)
			||
			! filter_var($to, FILTER_VALIDATE_URL)
		) {
			return;
		}

		global $wpdb;

		// @codingStandardsIgnoreStart cannot use `$wpdb->prepare` because it remove's the backslashes
		$wpdb->query(
			"UPDATE {$wpdb->postmeta} " .
			"SET `meta_value` = REPLACE(`meta_value`, '" . str_replace( '/', '\\\/', $from ) . "', '" . str_replace( '/', '\\\/', $to ) . "') " .
			"WHERE `meta_key` = '_elementor_data' AND `meta_value` LIKE '[%' ;"
		); // meta_value LIKE '[%' are json formatted
		// @codingStandardsIgnoreEnd

		$option_keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name from {$wpdb->options} WHERE `option_value` LIKE %s;",
				'%%' . $from . '%%'
			)
		);

		foreach ($option_keys as $single_key) {
			update_option($single_key->option_name, json_decode(str_replace(
				str_replace('/', '\\/', $from),
				str_replace('/', '\\/', $to),
				json_encode(get_option($single_key->option_name))
			), true));
		}
	}

	private function handle_brizy_posts() {
		if (! is_callable('Brizy_Editor_Storage_Common::instance')) {
			return;
		}

		$post_types = \Brizy_Editor_Storage_Common::instance()->get('post-types');

		if (empty($post_types) && ! is_array($post_types)) {
			return;
		}

		$post_ids = $this->get_pages($post_types);

		if (empty($post_ids) && ! is_array($post_ids)) {
			return;
		}

		foreach ($post_ids as $post_id) {
			$this->import_single_post($post_id);
		}
	}

	public function import_single_post($post_id = 0) {
		$is_brizy_post = get_post_meta($post_id, 'brizy_post_uid', true);

		if (! $is_brizy_post) {
			return;
		}

		update_post_meta($post_id, 'brizy_enabled', true);

		$post = \Brizy_Editor_Post::get((int) $post_id);
		$editor_data = $post->get_editor_data();

		$post->set_editor_data( $editor_data );
		$post->set_editor_version(BRIZY_EDITOR_VERSION);
		$post->set_needs_compile(true);
		$post->saveStorage();
	}

	private function get_pages($post_types = array()) {
		if (! $post_types) {
			return null;
		}

		$args = array(
			'post_type' => $post_types,

			// Query performance optimization.
			'fields' => 'ids',
			'no_found_rows' => true,
			'post_status' => 'publish',
			'posts_per_page' => -1,
		);

		$query = new \WP_Query($args);

		if (! $query->have_posts()) {
			return null;
		}

		return $query->posts;
	}

	public function maybe_activate_elementor_experimental_container() {
		if (! defined('ELEMENTOR_VERSION')) {
			return;
		}

		$current_demo = Plugin::instance()->demo->get_current_demo();

		if (! $current_demo) {
			return;
		}

		if (! isset($current_demo['demo'])) {
			return;
		}

		$demo_name = explode(':', $current_demo['demo']);

		if (! isset($demo_name[1])) {
			$demo_name[1] = '';
		}

		$demo = $demo_name[0];
		$builder = $demo_name[1];

		$demo_content = Plugin::instance()->demo->fetch_single_demo([
			'demo' => $demo,
			'builder' => $builder
		]);

		if (! $demo_content) {
			return;
		}

		if ($demo_content['builder'] !== 'elementor') {
			return;
		}

		if (! isset($demo_content['elementor_experiment_container'])) {
			return;
		}

		update_option('elementor_experiment-container', 'active');
	}

	public function update_counts_for_all_terms() {
		if (! function_exists('blocksy_manager')) {
			return;
		}

		$taxonomies = array_reduce(
			blocksy_manager()->post_types->get_supported_post_types(),
			function ($result, $item) {
				return array_unique(array_merge(
					$result,
					array_values(array_diff(
						get_object_taxonomies($item),
						['post_format']
					))
				));
			},
			[]
		);

		foreach ($taxonomies as $taxonomy) {
			if (! taxonomy_exists($taxonomy)) {
				continue;
			}

			$terms = get_terms($taxonomy, ['hide_empty' => false]);
			$term_taxonomy_ids = wp_list_pluck($terms, 'term_taxonomy_id');

			wp_update_term_count($term_taxonomy_ids, $taxonomy);
		}
	}

	public function patch_attachment_ids_in_mods() {
		$body = json_decode(file_get_contents('php://input'), true);

		if (! $body) {
			return;
		}

		$requestsPayload = [];

		if (isset($body['requestsPayload'])) {
			$requestsPayload = $body['requestsPayload'];
		}

		if (! isset($requestsPayload['processed_posts'])) {
			return;
		}

		$all_mods = get_theme_mods();

		foreach ($all_mods as $key => $val) {
			if ($key === 'header_placements') {
				$new_val = $val;

				foreach ($val['sections'] as $section_index => $section) {
					foreach ($section['items'] as $item_index => $item) {
						$new_val['sections'][$section_index][
							'items'
						][$item_index]['values'] = $this->patch_attachment_ids_in_array(
							$item['values'],
							$requestsPayload
						);
					}
				}

				set_theme_mod($key, $new_val);
			}

			if (
				$key === 'custom_logo'
				&&
				is_array($val)
				&&
				isset($val['desktop'])
			) {
				$new_val = $val;

				$desktop_val = intval($val['desktop']);

				if (isset($requestsPayload['processed_posts'][$desktop_val])) {
					$new_val['desktop'] = intval($requestsPayload['processed_posts'][$desktop_val]);
				}

				$tablet_val = intval($val['tablet']);

				if (isset($requestsPayload['processed_posts'][$tablet_val])) {
					$new_val['tablet'] = intval($requestsPayload['processed_posts'][$tablet_val]);
				}

				$mobile_val = intval($val['mobile']);

				if (isset($requestsPayload['processed_posts'][$mobile_val])) {
					$new_val['mobile'] = intval($requestsPayload['processed_posts'][$mobile_val]);
				}

				set_theme_mod($key, $new_val);
			}

			if (
				$key === 'custom_logo'
				&&
				is_numeric($val)
				&&
				isset($requestsPayload['processed_posts'][intval($val)])
			) {
				set_theme_mod(
					$key,
					intval($requestsPayload['processed_posts'][intval($val)])
				);
			}

			if (
				is_array($val)
				&&
				isset($val['attachment_id'])
				&&
				$val['attachment_id']
				&&
				isset(
					$requestsPayload['processed_posts'][
						intval($val['attachment_id'])
					]
				)
			) {
				$new_val = $val;

				$new_val['attachment_id'] = intval($requestsPayload['processed_posts'][
					intval($val['attachment_id'])
				]);

				set_theme_mod($key, $new_val);
			}
		}
	}

	public function patch_attachment_ids_in_array($array, $requestsPayload) {
		foreach ($array as $key => $val) {
			if (
				$key === 'custom_logo'
				&&
				is_array($val)
				&&
				isset($val['desktop'])
			) {
				$new_val = $val;

				$desktop_val = intval($val['desktop']);

				if (isset($requestsPayload['processed_posts'][$desktop_val])) {
					$new_val['desktop'] = intval($requestsPayload['processed_posts'][$desktop_val]);
				}

				$tablet_val = intval($val['tablet']);

				if (isset($requestsPayload['processed_posts'][$tablet_val])) {
					$new_val['tablet'] = intval($requestsPayload['processed_posts'][$tablet_val]);
				}

				$mobile_val = intval($val['mobile']);

				if (isset($requestsPayload['processed_posts'][$mobile_val])) {
					$new_val['mobile'] = intval($requestsPayload['processed_posts'][$mobile_val]);
				}

				$array[$key] = $new_val;
			}

			if (
				$key === 'custom_logo'
				&&
				is_numeric($val)
				&&
				isset($requestsPayload['processed_posts'][intval($val)])
			) {
				$array[$key] = intval($requestsPayload['processed_posts'][intval($val)]);
			}

			if (
				is_array($val)
				&&
				isset($val['attachment_id'])
				&&
				$val['attachment_id']
				&&
				isset(
					$requestsPayload['processed_posts'][
						intval($val['attachment_id'])
					]
				)
			) {
				$new_val = $val;

				$new_val['attachment_id'] = intval($requestsPayload['processed_posts'][
					intval($val['attachment_id'])
				]);

				$array[$key] = $new_val;
			}
		}

		return $array;
	}
}

