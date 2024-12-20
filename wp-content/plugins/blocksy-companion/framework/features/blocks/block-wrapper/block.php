<?php

namespace Blocksy\Editor\Blocks;

class BlockWrapper {
	public function __construct() {
		add_action('init', [$this, 'blocksy_block_wrapper_block']);
		add_action('enqueue_block_editor_assets', [$this, 'enqueue_admin']);

		add_filter(
			'block_categories_all',
			function($categories) {
				$widgets_category = array_search(
					'widgets',
					array_column($categories, 'slug')
				);

				return array_merge(
					array_slice($categories, 0, $widgets_category),
					[
						[
							'slug'  => 'blocksy-blocks',
							'title' => 'Blocksy'
						]
					],
					array_slice($categories, $widgets_category)
				);
			}
		);
	}

	public function blocksy_block_wrapper_block() {
		$block_data = [
			'api_version' => 3,
			'render_callback' => function ($attributes, $content, $block) {
				if (strpos($content, 'class="ct-') === false) {
					return '';
				}

				$class = ['ct-block-wrapper'];

				if (isset($attributes['className'])) {
					$class[] = $attributes['className'];
				}

				$attributes = wp_parse_args(
					$attributes,
					[
						'style' => [],
						'isCollapsible' => false,
						'defaultExpanded' => true,
					]
				);

				$wp_styles = wp_style_engine_get_styles(
					$attributes['style']
				);

				$wp_styles_css = isset($wp_styles['css']) ? $wp_styles['css'] : '';

				if ($attributes['isCollapsible']) {
					$blocks = $block->parsed_block['innerBlocks'];

					$heading_block_index = array_search(
						'core/heading',
						array_column($blocks, 'blockName')
					);

					if (
						$heading_block_index === 0
						&&
						count($blocks) > 1
					) {
						$id = '';

						$accordion_block = $blocks[$heading_block_index + 1];
						$accordion_block_html = render_block($accordion_block);

						$p = new \WP_HTML_Tag_Processor($accordion_block_html);

						$accordion_tag = 'DIV';

						while ($p->next_tag()) {
							if (
								$p->get_tag() === 'DIV'
								&&
								$p->has_class('ct-filter-widget-wrapper')
							) {
								$p->set_attribute('data-behaviour', 'drop-down');
								$p->set_attribute('aria-hidden', $attributes['defaultExpanded'] ? 'false' : 'true');

								break;
							}
						}

						$id = $p->get_attribute('id');
						$accordion_block_html = $p->get_updated_html();

						$heading_block = $blocks[$heading_block_index];
						$heading_block_html = render_block($heading_block);

						$p = new \WP_HTML_Tag_Processor($heading_block_html);

						$heading_tag = '';
						$header_tags = array( 'H1', 'H2', 'H3', 'H4', 'H5', 'H6' );

						while ($p->next_tag()) {
							if (in_array($p->get_tag(), $header_tags, true)) {
								$heading_tag = $p->get_tag();

								$p->add_class('ct-expandable-trigger');
								$p->set_attribute('aria-expanded', $attributes['defaultExpanded'] ? 'true' : 'false');
								$p->set_attribute('data-icon', 'arrow');

								break;
							}
						}

						$icon_content = '<svg class="ct-icon" width="10" height="10" viewBox="0 0 25 25"><path d="M.207 17.829 12.511 5.525l1.768 1.768L1.975 19.596z"></path><path d="m10.721 7.243 1.768-1.768L24.793 17.78l-1.768 1.767z"></path></svg>';

						$heading_block_html = preg_replace (
							'/<\\/' . strtolower($heading_tag) . '>/',
							$icon_content . '</' . strtolower($heading_tag) . '>',
							$p->get_updated_html(),
							1
						);

						$content = $heading_block_html . $accordion_block_html;
					}
				}

				return blocksy_html_tag(
					'div',
					array_merge(
						[
							'class' => implode(' ', $class)
						],
						(! empty($wp_styles_css) ? [
							'style' => $wp_styles_css,
						] : [])
					),
					$content
				);
			},
		];

		register_block_type('blocksy/widgets-wrapper', $block_data);
	}

	public function enqueue_admin() {
		if (! function_exists('get_plugin_data')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$data = get_plugin_data(BLOCKSY__FILE__);

		wp_enqueue_script(
			'blocksy/widgets-wrapper',
			BLOCKSY_URL . '/static/bundle/blocks/widgets-wrapper.js',
			['wp-blocks', 'wp-element', 'wp-block-editor'],
			$data['Version']
		);
	}
}
