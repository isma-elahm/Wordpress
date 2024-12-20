<?php

namespace Blocksy\Editor;

class Blocks {
	private $blocks = [];

	public function __construct() {
		// Mount at `after_setup_theme` to make sure the theme is loaded
		add_action(
			'after_setup_theme',
			[$this, 'mount'],
			50
		);
	}

	public function mount() {
		register_block_pattern_category('blocksy', [
			'label' => _x(
				'Blocksy',
				'Block pattern category',
				'blocksy-companion'
			),
			'description' => __(
				'Patterns that contain buttons and call to actions.',
				'blocksy-companion'
			),
		]);

		add_action('enqueue_block_editor_assets', function () {
			if (! function_exists('get_plugin_data')) {
				require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}

			$data = get_plugin_data(BLOCKSY__FILE__);

			$deps = [
				'wp-blocks',
				'wp-core-data',
				'wp-element',
				'wp-block-editor',
				'wp-server-side-render',
			];

			global $wp_customize;

			if ($wp_customize) {
				$deps[] = 'ct-customizer-controls';
			} else {
				$deps[] = 'ct-options-scripts';
			}

			$theme = blocksy_get_wp_parent_theme();

			wp_register_style(
				'blocksy-theme-blocks-editor-styles',
				get_template_directory_uri() . '/static/bundle/theme-blocks-editor-styles.min.css',
				[],
				$theme->get('Version')
			);

			wp_enqueue_script(
				'blocksy/gutenberg-blocks',
				BLOCKSY_URL . '/static/bundle/blocks/blocks.js',
				$deps,
				$data['Version']
			);

			$data = [
				'breadcrumb_home_item' => blocksy_get_theme_mod('breadcrumb_home_item', 'text'),
				'breadcrumb_home_text' => blocksy_get_theme_mod('breadcrumb_home_text', __('Home Page Text', 'blocksy-companion')),
				'breadcrumb_separator' => blocksy_get_theme_mod('breadcrumb_separator', 'type-1'),
				'breadcrumb_page_title' => blocksy_get_theme_mod('breadcrumb_page_title', 'yes') === 'yes',
			];

			wp_localize_script(
				'blocksy/gutenberg-blocks',
				'blc_blocks_data',
				$data
			);
		});

		$blocks = [
			'about-me',
			'contact-info',
			'quote',
			'socials',
			'search',
			'share-box'
		];

		foreach ($blocks as $block) {
			$this->blocks[$block] = new \Blocksy\Editor\GutenbergBlock(
				$block,
				[
					'static' => false,
				]
			);
		}

		add_action('wp_ajax_blocksy_get_dynamic_block_view', function () {
			if (
				! current_user_can('edit_posts')
				||
				! isset($this->blocks[$_POST['block']])
			) {
				wp_send_json_error();
			}

			$gutenberg_block = $this->blocks[$_POST['block']];

			wp_send_json_success([
				'content' => $gutenberg_block->render(
					json_decode(wp_unslash($_POST['attributes']), true)
				),
			]);
		});

		$this->init_blocks();
	}

	public function init_blocks() {
		// Root Block
		new \Blocksy\Editor\Blocks\BlockWrapper();

		new \Blocksy\Editor\Blocks\BreadCrumbs();
		new \Blocksy\Editor\Blocks\Query();
		new \Blocksy\Editor\Blocks\TaxQuery();
		new \Blocksy\Editor\Blocks\DynamicData();
	}
}
