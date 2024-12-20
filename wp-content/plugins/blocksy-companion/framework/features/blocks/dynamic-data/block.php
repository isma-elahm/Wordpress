<?php

namespace Blocksy\Editor\Blocks;

class DynamicData {
	public function __construct() {
		register_block_type(
			BLOCKSY_PATH . '/static/js/editor/blocks/dynamic-data/block.json',
			[
				'render_callback' => [$this, 'render'],
			]
		);

		add_filter('blocksy:gutenberg-blocks-data', function ($data) {
			$options = blocksy_akg(
				'options',
				blocksy_get_variables_from_file(
					dirname(__FILE__) . '/options.php',
					['options' => []]
				)
			);

			$options_name = 'dynamic-data';
			$data[$options_name] = $options;

			return $data;
		});

		add_action('rest_api_init', function() {
			register_rest_field('attachment', 'has_video', array(
				'get_callback' => function ($post, $field_name, $request) {
					if ($post['type'] !== 'attachment') {
						return null;
					}

					$maybe_new_video = blocksy_get_post_options($post['id']);
					$media_video_source = blocksy_akg('media_video_source', $maybe_new_video, 'upload');

					$video_url = '';

					if ($media_video_source === 'upload') {
						$video_url = blocksy_akg('media_video_upload', $maybe_new_video, '');
					}

					if ($media_video_source === 'youtube') {
						$video_url = blocksy_akg('media_video_youtube_url', $maybe_new_video, '');
					}

					if ($media_video_source === 'vimeo') {
						$video_url = blocksy_akg('media_video_vimeo_url', $maybe_new_video, '');
					}

					if (! empty($video_url)) {
						return true;
					}

					return false;
				}
			));
		});

		add_action(
			'wp_ajax_blocksy_blocks_retrieve_dynamic_data_descriptor',
			function () {
				if (! current_user_can('manage_options')) {
					wp_send_json_error();
				}

				$data = json_decode(file_get_contents('php://input'), true);

				// TODO: remove tmp override
				if (! isset($data['post_id'])) {
					$potential_post_types = blocksy_manager()->post_types->get_all();

					$fields = [];

					foreach ($potential_post_types as $single_cpt) {
						$fields = array_merge(
							$fields,
							$this->get_custom_fields($single_cpt)
						);
					}

					wp_send_json_success(apply_filters(
						'blocksy:general:blocks:dynamic-data:data',
						[
							'post_id' => null,
							'post_type' => 'post',
							'fields' => array_map(
								"unserialize",
								array_unique(
									array_map(
										"serialize",
										$fields
									)
								)
							),
							'dynamic_styles' => $this->get_dynamic_styles_for(),
							'$potential_post_types' => $potential_post_types
						]
					));
				}

				if (! $data || ! isset($data['post_id'])) {
					wp_send_json_error();
				}

				if (! function_exists('blc_get_ext')) {
					wp_send_json_error();
				}

				$post_type = get_post_type($data['post_id']);

				$fields = $this->get_custom_fields($post_type, $data['post_id']);

				wp_send_json_success(apply_filters(
					'blocksy:general:blocks:dynamic-data:data',
					[
						'post_id' => $data['post_id'],
						'post_type' => $post_type,
						'fields' => $fields,
						'dynamic_styles' => $this->get_dynamic_styles_for()
					]
				));
			}
		);

		add_action(
			'wp_ajax_blocksy_dynamic_data_block_custom_field_data',
			function () {
				if (! current_user_can('manage_options')) {
					wp_send_json_error();
				}

				$data = json_decode(file_get_contents('php://input'), true);

				if (
					! $data
					||
					! isset($data['post_id'])
					||
					! isset($data['field_provider'])
					||
					! isset($data['field_id'])
				) {
					wp_send_json_error();
				}

				$post_type = get_post_type($data['post_id']);

				if (
					$data['field_provider'] === 'woo'
					&&
					$data['field_id'] === 'brands'
				) {
					$brands = get_the_terms($data['post_id'], 'product_brands');

					$brands_result = [];

					foreach ($brands as $term) {
						$term_atts = get_term_meta(
							$term->term_id,
							'blocksy_taxonomy_meta_options'
						);

						if (empty($term_atts)) {
							$term_atts = [[]];
						}

						$term_atts = $term_atts[0];

						$maybe_image = blocksy_akg('icon_image', $term_atts, '');

						if (is_array($maybe_image)) {
							$attachment = $maybe_image;
						}

						$brands_result[] = [
							'term_id' => $term->term_id,
							'name' => $term->name,
							'slug' => $term->slug,
							'image' => $attachment
						];
					}

					wp_send_json_success([
						'post_id' => $data['post_id'],
						'post_type' => $post_type,
						'field_data' => $brands_result
					]);
				}

				$maybe_ext = blc_get_ext('post-types-extra');

				if (! $maybe_ext || ! $maybe_ext->dynamic_data) {
					wp_send_json_error();
				}

				$field_render = blc_get_ext('post-types-extra')
					->dynamic_data
					->get_field_to_render(
						[
							'id' => $data['field_provider'] . '_field',
							'field' => $data['field_id'],
						],
						[
							'post_type' => $post_type,
							'post_id' => $data['post_id'],
							'allow_images' => true
						]
					);

				wp_send_json_success([
					'post_id' => $data['post_id'],
					'post_type' => $post_type,
					'field_data' => $field_render['value']
				]);
			}
		);
	}

	public function render($attributes) {
		if (
			isset($attributes['lightbox'])
			&&
			$attributes['lightbox'] === 'yes'
		) {
			if (wp_script_is('wp-block-image-view', 'registered')) {
				wp_enqueue_script('wp-block-image-view');
			}

			if (function_exists('wp_scripts_get_suffix')) {
				wp_register_script_module(
					'@wordpress/block-library/image',
					includes_url("blocks/image/view.min.js"),
					array('@wordpress/interactivity'),
					defined('GUTENBERG_VERSION') ? GUTENBERG_VERSION : get_bloginfo('version')
				);

				wp_enqueue_script_module('@wordpress/block-library/image');
			}
		}

		$post_id = get_the_ID();

		$maybe_special_post_id = blocksy_get_special_post_id([
			'context' => 'local'
		]);

		$old_post = null;

		if ($maybe_special_post_id && $post_id !== $maybe_special_post_id) {
			global $post;

			$old_post = $post;

			$post = get_post($maybe_special_post_id);
			setup_postdata($post);
		}

		$content = blocksy_render_view(
			dirname(__FILE__) . '/view.php',
			[
				'attributes' => $attributes,
				'block_instance' => $this
			]
		);

		if ($old_post !== null) {
			wp_reset_postdata();
			$post = $old_post;
		}

		return $content;
	}

	public function get_dynamic_styles_for() {
		if (
			! function_exists('blc_get_ext')
			||
			! blc_get_ext('post-types-extra')
			||
			! blc_get_ext('post-types-extra')->taxonomies_customization
		) {
			return '';
		}

		$styles = [
			'desktop' => '',
			'tablet' => '',
			'mobile' => ''
		];

		$css = new \Blocksy_Css_Injector();
		$tablet_css = new \Blocksy_Css_Injector();
		$mobile_css = new \Blocksy_Css_Injector();

		blc_get_ext('post-types-extra')
			->taxonomies_customization
			->get_terms_dynamic_styles([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'context' => 'global',
				'chunk' => 'global'
			]);

		$styles['desktop'] .= $css->build_css_structure();
		$styles['tablet'] .= $tablet_css->build_css_structure();
		$styles['mobile'] .= $mobile_css->build_css_structure();

		$final_css = '';

		if (! empty($styles['desktop'])) {
			$final_css .= $styles['desktop'];
		}

		if (! empty(trim($styles['tablet']))) {
			$final_css .= '@media (max-width: 999.98px) {' . $styles['tablet'] . '}';
		}

		if (! empty(trim($styles['mobile']))) {
			$final_css .= '@media (max-width: 689.98px) {' . $styles['mobile'] . '}';
		}

		return $final_css;
	}

	public function get_custom_fields($post_type, $post_id = null) {
		$providers = [];

		if (
			function_exists('blc_get_ext')
			&&
			blc_get_ext('post-types-extra')
			&&
			blc_get_ext('post-types-extra')->dynamic_data
		) {
			$providers = [
				'acf',
				'metabox',
				'custom',
				'toolset',
				'jetengine',
				'pods',
				'acpt'
			];
		}

		$fields = [];

		foreach ($providers as $provider) {
			$maybe_fields = blc_get_ext('post-types-extra')
				->dynamic_data
				->retrieve_dynamic_data_fields([
					'post_type' => $post_type,
					'provider' => $provider,
					'allow_images' => true,
					'post_id' => $post_id,
				]);

			if (empty($maybe_fields)) {
				continue;
			}

			$result = [];

			foreach ($maybe_fields as $field => $label) {
				$field_render = blc_get_ext('post-types-extra')
					->dynamic_data
					->get_field_to_render(
						[
							'id' => $provider . '_field',
							'field' => $field,
						],
						[
							'post_type' => $post_type,
							'post_id' => $post_id,
							'allow_images' => true
						]
					);

				if (! $field_render) {
					continue;
				}

				$field_type = 'text';

				if (
					is_array($field_render['value'])
					&&
					isset($field_render['value']['type'])
				) {
					if ($field_render['value']['type'] === 'image') {
						$field_type = 'image';
					}
				}

				$result[] = [
					'id' => $field,
					'label' => $label,
					'type' => $field_type
				];
			}

			$fields[] = [
				'provider' => $provider,
				'fields' => $result
			];
		}

		return $fields;
	}
}


