<?php

if (! function_exists('blocksy_render_archive_cards')) {
	function blocksy_render_archive_cards($args = []) {
		global $wp_query;

		$args = wp_parse_args(
			$args,
			[
				'query' => $wp_query,
				'prefix' => blocksy_manager()->screen->get_prefix(),

				'has_slideshow' => false,
				'has_slideshow_arrows' => false,
				'has_slideshow_autoplay' => false,
				'has_slideshow_autoplay_speed' => 3,

				'render_no_posts' => true,

				'has_pagination' => true,
				'pagination_args' => []
			]
		);

		$blog_post_structure = blocksy_listing_page_structure([
			'prefix' => $args['prefix']
		]);

		if ($args['query']->have_posts()) {
			$entries_open = [
				'class' => 'entries',
			];

			$container_output = apply_filters(
				'blocksy:posts-listing:container:custom-output',
				null
			);

			$has_cards_type = true;

			if ($container_output) {
				$hook_id = blc_get_content_block_that_matches([
					'template_type' => 'archive'
				]);

				$atts = blocksy_get_post_options($hook_id);

				if (blocksy_akg(
					'has_template_default_layout',
					$atts,
					'yes'
				) !== 'yes') {
					$has_cards_type = false;
				}

				$entries_open['data-archive'] = "custom";
			} else {
				$entries_open['data-archive'] = "default";
			}

			$entries_open['data-layout'] = esc_attr($blog_post_structure);

			if ($has_cards_type) {
				$card_type = blocksy_get_listing_card_type([
					'prefix' => $args['prefix']
				]);

				if ($card_type) {
					$entries_open['data-cards'] = $card_type;
				}
			}

			$entries_open = array_merge(
				$entries_open,
				blocksy_schema_org_definitions(
					'blog',
					[
						'array' => true
					]
				)
			);

			$archive_order = blocksy_get_theme_mod(
				$args['prefix'] . '_archive_order',
				[]
			);

			foreach ($archive_order as $archive_layer) {
				if (! $archive_layer['enabled']) {
					continue;
				}

				if ($archive_layer['id'] === 'featured_image') {
					$hover_effect = blocksy_akg(
						'image_hover_effect',
						$archive_layer,
						'none'
					);

					if ($hover_effect !== 'none') {
						$entries_open['data-hover'] = $hover_effect;
					}
				}
			}

			$entries_open = array_merge(
				$entries_open,
				blocksy_generic_get_deep_link([
					'prefix' => $args['prefix'],
					'return' => 'array'
				])
			);

			do_action('blocksy:loop:before');

			if ($args['has_slideshow']) {
				$flexy_attr = array_merge(
					[
						'class' => 'flexy-container',
						'data-flexy' => 'no'
					],
					$args['has_slideshow_autoplay'] ? [
						'data-autoplay' => $args['has_slideshow_autoplay_speed']
					] : []
				);

				echo '<div ' . blocksy_attr_to_html($flexy_attr) . '>
						<div class="flexy">
							<div class="flexy-view" data-flexy-view="boxed">
								<div class="entries flexy-items columns-4" ' . blocksy_attr_to_html($entries_open) . '>';
			} else {
				echo '<div ' . blocksy_attr_to_html($entries_open) . '>';
			}

			while ($args['query']->have_posts()) {
				$args['query']->the_post();

				ob_start();
				blocksy_render_archive_card([
					'prefix' => $args['prefix']
				]);
				$item_content = ob_get_clean();

				if ($args['has_slideshow']) {
					$item_content = blocksy_html_tag(
						'div',
						[
							'class' => 'flexy-item'
						],
						$item_content
					);
				}

				echo $item_content;
			}

			echo '</div>';

			if ($args['has_slideshow']) {
				$arrows = '';

				if ($args['has_slideshow_arrows']) {
					$arrows = '<span class="flexy-arrow-prev">
						<svg width="16" height="10" fill="currentColor" viewBox="0 0 16 10">
							<path d="M15.3 4.3h-13l2.8-3c.3-.3.3-.7 0-1-.3-.3-.6-.3-.9 0l-4 4.2-.2.2v.6c0 .1.1.2.2.2l4 4.2c.3.4.6.4.9 0 .3-.3.3-.7 0-1l-2.8-3h13c.2 0 .4-.1.5-.2s.2-.3.2-.5-.1-.4-.2-.5c-.1-.1-.3-.2-.5-.2z"></path>
						</svg>
					</span>

					<span class="flexy-arrow-next">
						<svg width="16" height="10" fill="currentColor" viewBox="0 0 16 10">
							<path d="M.2 4.5c-.1.1-.2.3-.2.5s.1.4.2.5c.1.1.3.2.5.2h13l-2.8 3c-.3.3-.3.7 0 1 .3.3.6.3.9 0l4-4.2.2-.2V5v-.3c0-.1-.1-.2-.2-.2l-4-4.2c-.3-.4-.6-.4-.9 0-.3.3-.3.7 0 1l2.8 3H.7c-.2 0-.4.1-.5.2z"></path>
						</svg>
					</span>';
				}

				echo $arrows . '</div></div></div>';
			}

			do_action('blocksy:loop:after');

			/**
			 * Note to code reviewers: This line doesn't need to be escaped.
			 * Function blocksy_display_posts_pagination() used here escapes the value properly.
			 */
			if ($args['has_pagination']) {
				$args['pagination_args']['query'] = $args['query'];
				$args['pagination_args']['prefix'] = $args['prefix'];

				echo blocksy_display_posts_pagination($args['pagination_args']);
			}
		} else {
			if ($args['render_no_posts']) {
				get_template_part('template-parts/content', 'none');
			}
		}
	}
}

