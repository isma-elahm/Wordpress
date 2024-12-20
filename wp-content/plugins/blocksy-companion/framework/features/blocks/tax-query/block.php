<?php

namespace Blocksy\Editor\Blocks;

class TaxQuery {
	public function __construct() {
		add_action('wp_ajax_blocksy_get_tax_block_data', function () {
			if (! current_user_can('edit_posts')) {
				wp_send_json_error();
			}

			$body = json_decode(file_get_contents('php://input'), true);

			if (! isset($body['attributes'])) {
				wp_send_json_error();
			}

			$all_terms = $this->get_terms_for($body['attributes']);

			wp_send_json_success([
				'all_terms' => $all_terms
			]);
		});

		add_action('wp_ajax_blocksy_get_terms_block_patterns', function () {
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
						'blocksy/tax-query',
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

		register_block_type(
			BLOCKSY_PATH . '/static/js/editor/blocks/tax-query/block.json',
			[
				'render_callback' => function ($attributes, $content, $block) {
					if (strpos($content, 'wp-block-blocksy-tax-query"></div>') !== false) {
						return '';
					}

					$block_reader = new \WP_HTML_Tag_Processor($content);

					if (
						$block_reader->next_tag([
							'class_name' => 'wp-block-blocksy-tax-query'
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

		add_filter(
			'render_block',
			function ($block_content, $block, $instance) {
				if ($block['blockName'] !== 'blocksy/tax-template') {
					return $block_content;
				}

				$processor = new \WP_HTML_Tag_Processor($block_content);

				$context = $instance->context;

				$is_slideshow_layout = $context['has_slideshow'] === 'yes';
				$layout = blocksy_akg('type', $block['attrs']['layout'], 'default');
				$is_grid_layout = $layout === 'grid';

				$processor->next_tag('div');

				$class = array_merge(
					$is_slideshow_layout ? ['ct-query-template', 'is-layout-slider'] : ['ct-query-template-' . $layout],
				);

				if (
					! $is_slideshow_layout
					&&
					$layout !== 'grid'
				) {
					$class[] = 'is-layout-flow';
				}

				$gap_value = self::get_gap_value($block['attrs']);

				$processor->set_attribute('class', implode(' ', $class));
				$block_content = $processor->get_updated_html();

				$css = new \Blocksy_Css_Injector();
				$tablet_css = new \Blocksy_Css_Injector();
				$mobile_css = new \Blocksy_Css_Injector();

				$root_selector = ["[data-id='{$context['uniqueId']}']"];

				$alignmentStyles = [];

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

				$desktopColumns = blocksy_akg('columnCount', $block['attrs']['layout'], '3');
				$tabletColumns = blocksy_akg('tabletColumns', $block['attrs'], '2');
				$mobileColumns = blocksy_akg('mobileColumns', $block['attrs'], '1');

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

				if ($is_grid_layout && ! empty($gap)) {
					$css->put(
						blocksy_assemble_selector($root_selector),
						'--grid-columns-gap: ' . $gap
					);
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
			BLOCKSY_PATH . '/static/js/editor/blocks/tax-template/block.json',
			[
				'render_callback' => function ($attributes, $content, $block) {
					$all_terms = $this->get_terms_for($block->context);

					if (empty($all_terms)) {
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

					foreach ($all_terms as $key => $term) {
						$term_obj = get_term($term['term_id']);
						$block_instance = $block->parsed_block;

						// Set the block name to one that does not correspond to an existing registered block.
						// This ensures that for the inner instances of the Post Template block, we do not render any block supports.
						$block_instance['blockName'] = 'core/null';

						global $blocksy_term_obj;
						$blocksy_term_obj = $term_obj;

						// Render the inner blocks of the Post Template block with `dynamic` set to `false` to prevent calling
						// `render_callback` and ensure that no wrapper markup is included.
						$block_content = (new \WP_Block($block_instance))->render(
							['dynamic' => false]
						);

						$single_item = blocksy_html_tag(
							'div',
							[
								'class' => 'wp-block-term is-layout-flow',
							],
							$block_content
						);

						if ($is_slideshow_layout) {
							$single_item = blocksy_html_tag(
								'div',
								array_merge(
									[
										'class' => 'flexy-item',
									],
									$key === 0 ? ['data-item' => 'initial'] : []
								),
								$single_item
							);
						}

						$content .= $single_item;

						$blocksy_term_obj = null;
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
								$context['has_slideshow_autoplay'] === 'yes' ? [
									'data-autoplay' => $context['has_slideshow_autoplay_speed']
								] : []
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
										],
										$content
									)
								) .
								$arrows
							)
						);
					}

					$result = blocksy_safe_sprintf(
						'<div %1$s>%2$s</div>',
						$wrapper_attributes,
						$content
					);

					return $result;
				},
			]
		);

		add_action(
			'init',
			function () {
				$tax_block_patterns = [
					'tax-layout-1',
					'tax-layout-2',
					'tax-layout-3',
					'tax-layout-4',
				];

				foreach ($tax_block_patterns as $tax_block_pattern) {
					$pattern_data = blocksy_get_variables_from_file(
						__DIR__ . '/block-patterns/' . $tax_block_pattern . '.php',
						['pattern' => []]
					);

					register_block_pattern(
						'blocksy/' . $tax_block_pattern,
						$pattern_data['pattern']
					);
				}
			}
		);
	}

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

		return trim($combined_gap_value);
	}

	private static function get_attributes($attributes) {
		return wp_parse_args(
			$attributes,
			[
				'uniqueId' => '',

				'taxonomy' => 'category',
				'limit' => 5,
				'offset' => 0,
				'orderby' => 'none',
				'order' =>  'desc',
				'include_term_ids' => [],
				'exclude_term_ids' => [],
				'class' => '',

				// yes | no
				'has_slideshow' => 'no',
				'has_slideshow_arrows' => 'yes',
				'has_slideshow_autoplay' => 'no',
				'has_slideshow_autoplay_speed' => 3,
			],
		);
	}

	public function get_terms_for($attributes) {
		$attributes = self::get_attributes($attributes);

		if (! taxonomy_exists($attributes['taxonomy'])) {
			return [];
		}

		$ffiltered_include = [];
		$filtered_exclude = [];

		if (
			! empty($attributes['include_term_ids'])
			&&
			isset($attributes['include_term_ids'][$attributes['taxonomy']])
			&&
			isset($attributes['include_term_ids'][$attributes['taxonomy']]['strategy'])
			&&
			$attributes['include_term_ids'][$attributes['taxonomy']]['strategy'] === 'specific'
		) {
			foreach ($attributes['include_term_ids'][$attributes['taxonomy']]['terms'] as $key => $value) {
				$term = get_term_by('slug', $value, $attributes['taxonomy']);

				if ($term) {
					$filtered_include[] = $term->term_id;
				}
			}
		}

		if (
			! empty($attributes['exclude_term_ids'])
			&&
			isset($attributes['exclude_term_ids'][$attributes['taxonomy']])
			&&
			isset($attributes['exclude_term_ids'][$attributes['taxonomy']]['strategy'])
			&&
			$attributes['exclude_term_ids'][$attributes['taxonomy']]['strategy'] === 'specific'
		) {
			foreach ($attributes['exclude_term_ids'][$attributes['taxonomy']]['terms'] as $key => $value) {
				$term = get_term_by('slug', $value, $attributes['taxonomy']);

				if ($term) {
					$filtered_exclude[] = $term->term_id;
				}
			}
		}

		$terms_query_args = [
			'taxonomy' => $attributes['taxonomy'],
			'orderby' => $attributes['orderby'],
			'order' => $attributes['order'],
			'offset' => $attributes['offset'],
			'number' => $attributes['limit']
		];

		if (! empty($filtered_exclude)) {
			$terms_query_args['exclude'] = $filtered_exclude;
		}

		if (! empty($filtered_include)) {
			$terms_query_args['include'] = $filtered_include;
		}

		add_filter(
			'get_terms_orderby',
			[$this, 'allow_random_order_by_in_term_query'],
			10, 2
		);

		$terms = get_terms($terms_query_args);

		remove_filter(
			'get_terms_orderby',
			[$this, 'allow_random_order_by_in_term_query'],
			10, 2
		);

		if (
			is_wp_error($terms)
			||
			empty($terms)
		) {
			return [];
		}

		$terms_descriptors = [];

		foreach ($terms as $term) {
			$attachment = [];

			$id = get_term_meta($term->term_id, 'thumbnail_id');

			if (isset($id[0])) {
				$attachment = [
					'attachment_id' => $id[0],
					'url' => wp_get_attachment_image_url($id[0], 'full')
				];
			}

			$term_atts = get_term_meta(
				$term->term_id,
				'blocksy_taxonomy_meta_options'
			);

			if (empty($term_atts)) {
				$term_atts = [[]];
			}

			$term_atts = $term_atts[0];

			$maybe_icon = blocksy_akg('icon_image', $term_atts, '');
			$maybe_image = blocksy_akg('image', $term_atts, $attachment);

			$terms_descriptors[] = [
				'term_id' => $term->term_id,
				'icon' => $maybe_icon,
				'image' => $maybe_image,
			];
		}

		return $terms_descriptors;
	}

	public function allow_random_order_by_in_term_query($orderby, $query) {
		if ($query['orderby'] === 'rand') {
			return 'RAND()';
		}

		return $orderby;
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

