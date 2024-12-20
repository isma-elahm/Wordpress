<?php

namespace Blocksy\Editor\Blocks;

class Query {
	public function __construct() {
		add_action('wp_ajax_blocksy_get_posts_block_data', function () {
			if (! current_user_can('edit_posts')) {
				wp_send_json_error();
			}

			$body = json_decode(file_get_contents('php://input'), true);

			if (! isset($body['attributes'])) {
				wp_send_json_error();
			}

			$all_post_types = [
				'post' => __('Posts', 'blocksy-companion')
			];

			$post_types = blocksy_manager()->post_types->get_supported_post_types();

			foreach ($post_types as $single_post_type) {
				$post_type_object = get_post_type_object($single_post_type);

				if (! $post_type_object) {
					continue;
				}

				$all_post_types[
					$single_post_type
				] = $post_type_object->labels->singular_name;
			}

			if (! isset($body['previewedPostId'])) {
				$body['previewedPostId'] = 0;
			}

			$context = [
				'post_id' => $body['previewedPostId'],
				'purpose' => 'editor'
			];

			$query = $this->get_query_for(
				$body['attributes'],
				$context
			);

			$attributes = self::get_attributes($body['attributes']);

			$prefix = self::get_prefix_for($body['attributes']);

			$pagination_data = $this->get_pagination_descriptor($body['attributes']);

			$final_block_data = [
				'taxonomies' => blocksy_get_taxonomies_for_cpt(
					get_post_type($body['previewedPostId']),
					['return_empty' => true]
				),
				'all_posts' => $query->get_posts(),
				'post_types' => $all_post_types,
				'pagination_output' => blocksy_display_posts_pagination([
					'query' => $query,
					'prefix' => $prefix,
					'query_var' => $pagination_data['query_var']
				])
			];

			if (
				isset($attributes['design'])
				&&
				$attributes['design'] === 'default'
			) {
				$final_block_data['customizer_preview'] = $this->render_block($body['attributes'], $context);
			}

			wp_send_json_success($final_block_data);
		});

		add_action('wp_ajax_blocksy_get_posts_block_patterns', function () {
			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			$all_patterns = \WP_Block_Patterns_Registry::get_instance()
				->get_all_registered();

			$result = [];

			foreach ($all_patterns as $single_pattern) {
				if (
					isset($single_pattern['blockTypes'])
					&&
					is_array($single_pattern['blockTypes'])
					&&
					in_array(
						'blocksy/query',
						$single_pattern['blockTypes']
					)
				) {
					$result[] = $single_pattern;
				}
			}

			wp_send_json_success([
				'patterns' => $result
			]);
		});

		add_filter(
			'render_block',
			function ($block_content, $block, $instance) {
				if ($block['blockName'] !== 'blocksy/post-template') {
					return $block_content;
				}

				$processor = new \WP_HTML_Tag_Processor($block_content);

				$context = $instance->context;

				$is_slideshow_layout = $context['has_slideshow'] === 'yes';
				$layout = blocksy_akg('layout/type', $block['attrs'], 'default');
				$is_grid_layout = $layout === 'grid';

				$processor->next_tag('div');

				$class = ['ct-query-template-' . $layout];

				if ($is_slideshow_layout) {
					$class = ['ct-query-template', 'is-layout-slider'];
				}

				if (
					! $is_slideshow_layout
					&&
					$layout !== 'grid'
				) {
					$class[] = 'is-layout-flow';
				}

				$processor->set_attribute('class', implode(' ', $class));
				$block_content = $processor->get_updated_html();

				$css = new \Blocksy_Css_Injector();
				$tablet_css = new \Blocksy_Css_Injector();
				$mobile_css = new \Blocksy_Css_Injector();

				$root_selector = ["[data-id='{$context['uniqueId']}']"];

				if (
					isset($block['attrs']['verticalAlignment'])
					&&
					$is_grid_layout
				) {
					$alignItems = 'center';

					if ($block['attrs']['verticalAlignment'] === 'top') {
						$alignItems = 'flex-start';
					}

					if ($block['attrs']['verticalAlignment'] === 'bottom') {
						$alignItems = 'flex-end';
					}

					$css->put(
						blocksy_assemble_selector($root_selector),
						'align-items: ' . $alignItems
					);
				}

				$columns = [
					'desktop' => 1,
					'tablet' => 1,
					'mobile' => 1
				];

				if ($is_grid_layout) {
					$columns = [
						'desktop' => blocksy_akg(
							'columnCount',
							$block['attrs']['layout'],
							'3'
						),

						'tablet' => blocksy_akg(
							'tabletColumns',
							$block['attrs'],
							'2'
						),

						'mobile' => blocksy_akg(
							'mobileColumns',
							$block['attrs'],
							'1'
						)
					];

					if (! $columns['desktop']) {
						$columns['desktop'] = 3;
					}

					if (! $columns['tablet']) {
						$columns['tablet'] = 2;
					}

					if (! $columns['mobile']) {
						$columns['mobile'] = 1;
					}
				}

				$gap = self::get_gap_value($block['attrs']);

				if ($is_grid_layout) {
					if (! empty($gap)) {
						$css->put(
							blocksy_assemble_selector($root_selector),
							'--grid-columns-gap: ' . $gap
						);
					}
				}

				if (
					! $is_grid_layout
					&&
					! empty($gap)
					&&
					! $is_slideshow_layout
				) {
					// DO WE REALLY NEED GAP HERE???

					$css->put(
						blocksy_assemble_selector(
							blocksy_mutate_selector([
								'selector' => $root_selector,
								'operation' => 'suffix',
								'to_add' => ':where(.is-layout-flow > *)'
							])
						),

						'margin-block-end: ' . $gap
					);
				}

				$this->get_grid_styles([
					'css' => $css,
					'tablet_css' => $tablet_css,
					'mobile_css' => $mobile_css,

					'root_selector' => $root_selector,

					'is_slider' => $is_slideshow_layout,
					'layout_type' => $is_grid_layout ? 'grid' : 'default',

					'columns' => $columns
				]);

				wp_style_engine_get_stylesheet_from_css_rules(
					array_merge(
						$css->get_wp_style_engine_rules([
							'device' => 'desktop'
						]),
						$tablet_css->get_wp_style_engine_rules([
							'device' => 'tablet'
						]),
						$mobile_css->get_wp_style_engine_rules([
							'device' => 'mobile'
						])
					),
					[
						'context'  => 'block-supports',
						'prettify' => false,
						'optimize' => true
					]
				);

				return $block_content;
			},
			11,
			3
		);

		register_block_type(
			BLOCKSY_PATH . '/static/js/editor/blocks/query/block.json',
			[
				'render_callback' => function ($attributes, $content, $block) {
					if (
						empty($block->inner_blocks)
						&&
						isset($attributes['design'])
						&&
						$attributes['design'] === 'default'
					) {
						$content = $this->render_block($attributes);

						if (empty($content)) {
							return '';
						}

						$wrapper_attr = [];

						$border_result = get_block_core_post_featured_image_border_attributes(
							$attributes
						);

						if (! empty($border_result['class'])) {
							$wrapper_attr['class'] = $border_result['class'];
						}

						if (! empty($border_result['style'])) {
							$wrapper_attr['style'] = $border_result['style'];
						}

						if (! empty($attributes['uniqueId'])) {
							$wrapper_attr['data-id'] = $attributes['uniqueId'];
						}

						$wrapper_attributes = get_block_wrapper_attributes($wrapper_attr);

						return blocksy_html_tag(
							'div',
							$wrapper_attributes,
							$content
						);
					}

					if (strpos($content, 'wp-block-blocksy-query"></div>') !== false) {
						return '';
					}

					$inner_content = $block->inner_content;

					$block_reader = new \WP_HTML_Tag_Processor($content);

					if (
						$block_reader->next_tag([
							'class_name' => 'wp-block-blocksy-query'
						])
						&&
						! empty($attributes['uniqueId'])
					) {
						$block_reader->set_attribute(
							'data-id',
							$attributes['uniqueId']
						);
					}

					return $block_reader->get_updated_html();
				}
			]
		);

		register_block_type(
			BLOCKSY_PATH . '/static/js/editor/blocks/post-template/block.json',
			[
				'render_callback' => function ($attributes, $content, $block) {
					$query = $this->get_query_for($block->context);

					if (! $query->have_posts()) {
						return '';
					}

					$attributes = wp_parse_args(
						$attributes,
						[
							'desktopColumns' => '3',
							'tabletColumns' => '2',
							'mobileColumns' => '1',
						]
					);

					$context = wp_parse_args(
						$block->context,
						[
							'has_slideshow' => 'no',
							'has_slideshow_arrows' => 'yes',
							'has_slideshow_autoplay' => 'no',
							'has_slideshow_autoplay_speed' => 3,
						]
					);

					$is_slideshow_layout = $context['has_slideshow'] === 'yes';

					$content = '';

					$wrapper_attributes = get_block_wrapper_attributes();

					while ($query->have_posts()) {
						$query->the_post();

						$block_instance = $block->parsed_block;

						// Set the block name to one that does not correspond to an existing registered block.
						// This ensures that for the inner instances of the Post Template block, we do not render any block supports.
						$block_instance['blockName'] = 'core/null';

						$post_id = get_the_ID();
						$post_type = get_post_type();

						$filter_block_context = static function ($context) use ($post_id, $post_type) {
							$context['postType'] = $post_type;
							$context['postId'] = $post_id;

							return $context;
						};

						// Use an early priority to so that other 'render_block_context' filters have access to the values.
						add_filter('render_block_context', $filter_block_context, 1);

						// Render the inner blocks of the Post Template block with `dynamic` set to `false` to prevent calling
						// `render_callback` and ensure that no wrapper markup is included.
						$block_content = (new \WP_Block($block_instance))->render(
							['dynamic' => false]
						);
						remove_filter('render_block_context', $filter_block_context, 1);

						// Wrap the render inner blocks in a `li` element with the appropriate post classes.
						$post_classes = implode(' ', get_post_class('wp-block-post is-layout-flow'));

						$single_item = '<article' . ' class="' . esc_attr($post_classes) . '">' . $block_content . '</article>';

						if ($is_slideshow_layout) {
							$single_item = blocksy_html_tag(
								'div',
								[
									'class' => 'flexy-item',
								],
								$single_item
							);
						}

						$content .= $single_item;
					}

					if ($is_slideshow_layout) {
						$arrows = '';

						if ($context['has_slideshow_arrows'] === 'yes') {
							$arrows = '<span class="flexy-arrow-prev" data-position="outside">
								<svg width="16" height="10" fill="currentColor" viewBox="0 0 16 10">
									<path d="M15.3 4.3h-13l2.8-3c.3-.3.3-.7 0-1-.3-.3-.6-.3-.9 0l-4 4.2-.2.2v.6c0 .1.1.2.2.2l4 4.2c.3.4.6.4.9 0 .3-.3.3-.7 0-1l-2.8-3h13c.2 0 .4-.1.5-.2s.2-.3.2-.5-.1-.4-.2-.5c-.1-.1-.3-.2-.5-.2z"></path>
								</svg>
							</span>

							<span class="flexy-arrow-next" data-position="outside">
								<svg width="16" height="10" fill="currentColor" viewBox="0 0 16 10">
									<path d="M.2 4.5c-.1.1-.2.3-.2.5s.1.4.2.5c.1.1.3.2.5.2h13l-2.8 3c-.3.3-.3.7 0 1 .3.3.6.3.9 0l4-4.2.2-.2V5v-.3c0-.1-.1-.2-.2-.2l-4-4.2c-.3-.4-.6-.4-.9 0-.3.3-.3.7 0 1l2.8 3H.7c-.2 0-.4.1-.5.2z"></path>
								</svg>
							</span>';
						}

						$content = blocksy_html_tag(
							'div',
							array_merge(
								[
									'class' => 'flexy-container',
									'data-flexy' => 'no',
								],
								$context['has_slideshow_autoplay'] === 'yes' ? ['data-autoplay' => $context['has_slideshow_autoplay_speed']] : []
							),
							blocksy_html_tag(
								'div',
								[
									'class' => 'flexy'
								],
								blocksy_html_tag(
									'div',
									[
										'class' => 'flexy-view',
										'data-flexy-view' => 'boxed'
									],
									blocksy_html_tag(
										'div',
										[
											'class' => 'flexy-items',
											'data-height' => 'dynamic'
										],
										$content
									)
								) .
								$arrows
							)
						);
					}

					/*
					 * Use this function to restore the context of the template tags
					 * from a secondary query loop back to the main query loop.
					 * Since we use two custom loops, it's safest to always restore.
					 */
					wp_reset_postdata();

					$result = blocksy_safe_sprintf(
						'<div %1$s>%2$s</div>',
						$wrapper_attributes,
						$content
					);

					if (
						blocksy_akg('has_pagination', $context, 'no') === 'yes'
						&&
						! $is_slideshow_layout
					) {
						$prefix = self::get_prefix_for($block->context);

						$pagination_data = $this->get_pagination_descriptor($block->context);

						$result .= blocksy_display_posts_pagination([
							'query' => $query,
							'prefix' => $prefix,
							'query_var' => $pagination_data['query_var']
						]);
					}

					return $result;
				},
			]
		);

		add_action(
			'init',
			function () {
				$posts_block_patterns = [
					'posts-layout-1',
					'posts-layout-2',
					'posts-layout-3',
					'posts-layout-4',
					'posts-layout-5',
				];

				foreach ($posts_block_patterns as $posts_block_pattern) {
					$pattern_data = blocksy_get_variables_from_file(
						__DIR__ . '/block-patterns/' . $posts_block_pattern . '.php',
						['pattern' => []]
					);

					register_block_pattern(
						'blocksy/' . $posts_block_pattern,
						$pattern_data['pattern']
					);
				}
			}
		);
	}

	// TODO: maybe move this to utils
	private static function get_gap_value($attributes) {
		if (! isset($attributes['style'])) {
			return '';
		}

		$gap_value = '';

		$gap_value = blocksy_akg('spacing', $attributes['style'], []);

		if (! isset($gap_value['blockGap'])) {
			return '';
		}

		$gap_value = $gap_value['blockGap'];

		$combined_gap_value = '';
		$gap_sides = is_array($gap_value) ? ['top', 'left'] : ['top'];

		foreach ($gap_sides as $gap_side) {
			$process_value = $gap_value;

			if (is_array($gap_value)) {
				$process_value = isset($gap_value[$gap_side]) ? $gap_value[$gap_side] : '';
			}

			if (
				is_string($process_value)
				&&
				str_contains($process_value, 'var:preset|spacing|')
			) {
				$index_to_splice = strrpos($process_value, '|') + 1;
				$slug            = _wp_to_kebab_case(substr($process_value, $index_to_splice));
				$process_value   = "var(--wp--preset--spacing--$slug)";
			}

			$combined_gap_value .= "$process_value ";
		}

		if (trim($combined_gap_value) === '0') {
			return '0px';
		}

		return trim($combined_gap_value);
	}

	private static function get_attributes($attributes) {
		return wp_parse_args(
			$attributes,
			[
				'uniqueId' => '',

				'post_type' => 'post',
				'limit' => 5,

				'offset' => 0,

				// post_date | comment_count
				'orderby' => 'post_date',
				'order' => 'DESC',

				// yes | no
				'has_slideshow' => 'no',
				'has_slideshow_arrows' => 'yes',
				'has_slideshow_autoplay' => 'no',
				'has_slideshow_autoplay_speed' => 3,

				// yes | no
				'has_pagination' => 'no',

				// include | exclude | only
				'sticky_posts' => 'include',

				// 404 | skip
				'no_results' => '404',

				'class' => ''
			],
		);
	}

	public function render_block($attributes, $context = []) {
		$context = wp_parse_args($context, [
			'post_id' => get_the_ID(),

			// editor | frontend
			'purpose' => 'frontend'
		]);

		$attributes = self::get_attributes($attributes);

		$query = $this->get_query_for($attributes, $context);

		if (! $query->have_posts() && $attributes['no_results'] === 'skip') {
			return;
		}

		$prefix = self::get_prefix_for($attributes);

		$block_atts = [
			'class' => 'ct-posts-block',
			'data-prefix' => $prefix
		];

		if ($attributes['has_slideshow'] === 'yes') {
			$block_atts['class'] .= ' is-layout-slider';
		}

		if (! empty($attributes['class'])) {
			$block_atts['class'] .= ' ' . esc_attr($attributes['class']);
		}

		$result = '';

		global $wp_query;

		$prev_query = $wp_query;

		if (wp_doing_ajax()) {
			$wp_query = $query;
		}

		$pagination_data = $this->get_pagination_descriptor($attributes);

		ob_start();

		blocksy_render_archive_cards([
			'prefix' => $prefix,
			'query' => $query,
			'has_slideshow' => $attributes['has_slideshow'] === 'yes',
			'has_slideshow_arrows' => $attributes['has_slideshow_arrows'] === 'yes',
			'has_slideshow_autoplay' => $attributes['has_slideshow_autoplay'] === 'yes',
			'has_slideshow_autoplay_speed' => $attributes['has_slideshow_autoplay_speed'],
			'has_pagination' => $attributes['has_pagination'] === 'yes' && $attributes['has_slideshow'] !== 'yes',
			'pagination_args' => [
				'query_var' => $pagination_data['query_var'],
			],
			'render_no_posts' => false
		]);

		$result .= ob_get_clean();

		wp_reset_postdata();

		if (wp_doing_ajax()) {
			$wp_query = $prev_query;
		}

		$css = new \Blocksy_Css_Injector();
		$tablet_css = new \Blocksy_Css_Injector();
		$mobile_css = new \Blocksy_Css_Injector();

		if ($attributes['has_slideshow'] === 'yes') {
			$structure = blocksy_get_theme_mod($prefix . '_structure', 'grid');

			$grid_columns = blocksy_expand_responsive_value(
				blocksy_get_theme_mod(
					$prefix . '_columns',
					[
						'desktop' => 3,
						'tablet' => 2,
						'mobile' => 1
					]
				)
			);

			$this->get_grid_styles([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,

				'root_selector' => ["[data-id='{$attributes['uniqueId']}']"],

				'is_slider' => true,
				'layout_type' => $structure === 'grid' ? 'grid' : 'default',

				'columns' => $grid_columns
			]);
		}

		$final_css = '';

		if ($context['purpose'] === 'editor') {
			$this->get_customizer_styles_for($attributes, [
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css
			]);

			$styles = [
				'desktop' => '',
				'tablet' => '',
				'mobile' => ''
			];

			$styles['desktop'] .= $css->build_css_structure();
			$styles['tablet'] .= $tablet_css->build_css_structure();
			$styles['mobile'] .= $mobile_css->build_css_structure();

			if (! empty($styles['desktop'])) {
				$final_css .= $styles['desktop'];
			}

			if (! empty(trim($styles['tablet']))) {
				$final_css .= '@media (max-width: 999.98px) {' . $styles['tablet'] . '}';
			}

			if (! empty(trim($styles['mobile']))) {
				$final_css .= '@media (max-width: 689.98px) {' . $styles['mobile'] . '}';
			}
		}

		if ($context['purpose'] === 'frontend') {
			wp_style_engine_get_stylesheet_from_css_rules(
				array_merge(
					$css->get_wp_style_engine_rules([
						'device' => 'desktop'
					]),
					$tablet_css->get_wp_style_engine_rules([
						'device' => 'tablet'
					]),
					$mobile_css->get_wp_style_engine_rules([
						'device' => 'mobile'
					])
				),
				[
					'context'  => 'block-supports',
					'prettify' => false,
					'optimize' => true
				]
			);
		}

		if (! empty($final_css)) {
			$final_css = blocksy_html_tag(
				'style',
				[],
				$final_css
			);
		}

		if (empty($result)) {
			return '';
		}

		return blocksy_html_tag(
			'div',
			$block_atts,
			$result
		) . $final_css;
	}

	public function get_query_for($attributes, $context = []) {
		$attributes = self::get_attributes($attributes);

		$context = wp_parse_args($context, [
			'post_id' => get_the_ID()
		]);

		$query_args = [
			'order' => $attributes['order'],
			'post_type' => explode(',', $attributes['post_type']),
			'orderby' => $attributes['orderby'],
			'posts_per_page' => $attributes['limit'],
			'post_status' => 'publish'
		];

		if ($attributes['sticky_posts'] === 'exclude') {
			// $query_args['ignore_sticky_posts'] = true;
			$query_args['post__not_in'] = get_option('sticky_posts');
		}

		if ($attributes['sticky_posts'] === 'only') {
			$sticky_posts = get_option('sticky_posts');
			$query_args['ignore_sticky_posts'] = true;

			if (! empty($sticky_posts)) {
				$query_args['post__in'] = $sticky_posts;
			}
		}

		if ($attributes['has_pagination'] === 'yes') {
			$pagination_data = $this->get_pagination_descriptor($attributes);
			$query_args['paged'] = $pagination_data['value'];
		}

		if ($attributes['offset'] !== 0) {
			$current_page = 1;

			if ($attributes['has_pagination'] === 'yes') {
				$current_page = $query_args['paged'];
				$current_page = max( 1, $current_page );
			}

			$per_page = $attributes['limit'];
			$offset_start = $attributes['offset'];
			$query_args['offset'] = ( $current_page - 1 ) * $per_page + $offset_start;
		}

		$to_include = [
			'relation' => 'OR'
		];

		$to_exclude = [
			'relation' => 'AND'
		];

		foreach ($attributes['include_term_ids'] as $term_slug => $term_descriptor) {
			if ($term_descriptor['strategy'] === 'all') {
				continue;
			}

			if (
				$term_descriptor['strategy'] === 'specific'
				&&
				! empty($term_descriptor['terms'])
			) {
				$terms_to_pass = [];

				foreach ($term_descriptor['terms'] as $internal_term_slug) {
					if (function_exists('pll_get_term')) {
						$all_terms = get_terms(array(
							'taxonomy' => $term_slug,
							'slug' => esc_attr($internal_term_slug),
							'lang' => ''
						));

						if (empty($all_terms)) {
							continue;
						}

						$term = $all_terms[0];

						if (! $term) {
							continue;
						}

						$current_lang = blocksy_get_current_language();
						$current_term_id = pll_get_term($term->term_id, $current_lang);

						$term = get_term_by('id', $current_term_id, $term_slug);

						$internal_term_slug = $term->slug;
					}

					$terms_to_pass[] = $internal_term_slug;
				}

				$to_include[] = [
					'field' => 'slug',
					'taxonomy' => $term_slug,
					'terms' => $terms_to_pass
				];
			}

			if (
				$term_descriptor['strategy'] === 'related'
				&&
				$context['post_id']
			) {
				$post = get_post($context['post_id']);

				$current_post_type = get_post_type($post);

				if ($current_post_type !== $attributes['post_type']) {
					continue;
				}

				$all_taxonomies = get_the_terms($post->ID, $term_slug);

				if (! $all_taxonomies || is_wp_error($all_taxonomies)) {
					continue;
				}

				$all_taxonomy_ids = [];

				foreach ($all_taxonomies as $current_taxonomy) {
					if (! isset($current_taxonomy->term_id)) {
						continue;
					}

					$current_term_id = $current_taxonomy->term_id;

					if (function_exists('pll_get_term')) {
						$current_lang = blocksy_get_current_language();
						$current_term_id = pll_get_term($current_term_id, $current_lang);
					}

					if (! $current_term_id) {
						continue;
					}

					$all_taxonomy_ids[] = $current_term_id;
				}

				$query_args['post__not_in'] = [$post->ID];

				if (! empty($all_taxonomy_ids)) {
					$to_include[] = [
						'field' => 'term_id',
						'taxonomy' => $term_slug,
						'terms' => $all_taxonomy_ids
					];
				}
			}
		}

		foreach ($attributes['exclude_term_ids'] as $term_slug => $term_descriptor) {
			if ($term_descriptor['strategy'] === 'all') {
				continue;
			}

			if (
				$term_descriptor['strategy'] === 'specific'
				&&
				! empty($term_descriptor['terms'])
			) {
				$terms_to_pass = [];

				foreach ($term_descriptor['terms'] as $internal_term_slug) {
					if (function_exists('pll_get_term')) {
						$all_terms = get_terms(array(
							'taxonomy' => $term_slug,
							'slug' => esc_attr($internal_term_slug),
							'lang' => ''
						));

						if (empty($all_terms)) {
							continue;
						}

						$term = $all_terms[0];

						if (! $term) {
							continue;
						}

						$current_lang = blocksy_get_current_language();
						$current_term_id = pll_get_term($term->term_id, $current_lang);

						$term = get_term_by('id', $current_term_id, $term_slug);

						$internal_term_slug = $term->slug;
					}

					$terms_to_pass[] = $internal_term_slug;
				}

				$to_exclude[] = [
					'field' => 'slug',
					'taxonomy' => $term_slug,
					'terms' => $terms_to_pass,
					'operator' => 'NOT IN'
				];
			}
		}

		$tax_query = [];

		if (count($to_include) > 1) {
			$tax_query = array_merge($to_include, $tax_query);
		}

		if (count($to_exclude) > 1) {
			$tax_query = array_merge($to_exclude, $tax_query);
		}

		if (count($to_include) > 1 && count($to_exclude) > 1) {
			$tax_query['relation'] = 'AND';
		}

		$query_args['tax_query'] = $tax_query;


		if ($attributes['sticky_posts'] === 'include') {
			add_action('pre_get_posts', [$this, 'pre_get_posts']);
		}

		$query = apply_filters(
			'blocksy:general:blocks:query:custom',
			null,
			$query_args,
			$attributes
		);

		if (! $query) {
			$query = new \WP_Query();

			$query->query(apply_filters(
				'blocksy:general:blocks:query:args',
				$query_args,
				$attributes
			));
		}

		if ($attributes['sticky_posts'] === 'include') {
			remove_action('pre_get_posts', [$this, 'pre_get_posts']);
		}

		return $query;
	}

	public function pre_get_posts($q) {
		$q->is_home = true;
	}

	public function get_customizer_styles_for($attributes, $args = []) {
		$args = wp_parse_args($args, [
			'css' => null,
			'tablet_css' => null,
			'mobile_css' => null
		]);

		$prefix = self::get_prefix_for($attributes);

		$styles = [
			'desktop' => '',
			'tablet' => '',
			'mobile' => ''
		];

		$css = new \Blocksy_Css_Injector();
		$tablet_css = new \Blocksy_Css_Injector();
		$mobile_css = new \Blocksy_Css_Injector();

		blocksy_theme_get_dynamic_styles([
			'name' => 'global/posts-listing',
			'css' => $args['css'],
			'mobile_css' => $args['mobile_css'],
			'tablet_css' => $args['tablet_css'],
			'context' => 'global',
			'chunk' => 'global',
			'prefixes' => [$prefix]
		]);
	}

	private static function get_prefix_for($attributes) {
		$attributes = self::get_attributes($attributes);

		$prefix = 'blog';

		$custom_post_types = blocksy_manager()->post_types->get_supported_post_types();

		$preferred_post_type = explode(',', $attributes['post_type'])[0];

		foreach ($custom_post_types as $cpt) {
			if ($cpt === $preferred_post_type) {
				$prefix = $cpt . '_archive';
			}
		}

		return $prefix;
	}

	// ?query-1-page=2
	public function get_pagination_descriptor($attributes) {
		$attributes = self::get_attributes($attributes);

		$query_var = 'query-' . $attributes['uniqueId'];

		$current_page = 1;

		if (isset($_GET[$query_var])) {
			$current_page = intval(sanitize_text_field($_GET[$query_var]));
		}

		return [
			'query_var' => $query_var,
			'value' => $current_page
		];
	}

	private function get_grid_styles($args = []) {
		$args = wp_parse_args($args, [
			'css' => null,
			'tablet_css' => null,
			'mobile_css' => null,

			'root_selector' => '',

			// default | grid
			'layout_type' => 'default',

			'columns' => [
				'desktop' => 1,
				'tablet' => 1,
				'mobile' => 1
			],

			'is_slider' => false
		]);

		$css = $args['css'];
		$tablet_css = $args['tablet_css'];
		$mobile_css = $args['mobile_css'];

		if ($args['layout_type'] === 'grid') {
			$gridColumnsWidth = [
				'desktop' => $args['columns']['desktop'],
				'tablet' => $args['columns']['tablet'],
				'mobile' => $args['columns']['mobile']
			];

			if ($args['is_slider']) {
				$gridColumnsWidth['desktop'] = round(
					100 / $gridColumnsWidth['desktop'],
					2
				) . '%';

				$gridColumnsWidth['tablet'] = round(
					100 / $gridColumnsWidth['tablet'],
					2
				) . '%';

				$gridColumnsWidth['mobile'] = round(
					100 / $gridColumnsWidth['mobile'],
					2
				) . '%';
			}

			blocksy_output_responsive([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => blocksy_assemble_selector($args['root_selector']),
				'variableName' => 'grid-columns-width',
				'value' => $gridColumnsWidth,
				'unit' => ''
			]);
		}

		if ($args['is_slider']) {
			$selectors = [
				'desktop' => '',
				'tablet' => '',
				'mobile' => ''
			];

			foreach ($selectors as $device => $selector) {
				$selectors[$device] = blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => blocksy_mutate_selector([
							'selector' => $args['root_selector'],
							'operation' => 'suffix',
							'to_add' => '[data-flexy*="no"]'
						]),
						'operation' => 'suffix',
						'to_add' => '.flexy-item:nth-child(n + ' . (intval($args['columns'][$device]) + 1) . ')'
					])
				);
			}

			blocksy_output_responsive([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => $selectors,
				'variableName' => 'height',
				'variableType' => 'property',
				'value' => '1'
			]);
		}
	}
}
