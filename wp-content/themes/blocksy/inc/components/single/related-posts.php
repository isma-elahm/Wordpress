<?php

if (! function_exists('blocksy_render_related_card')) {
	function blocksy_render_related_card($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'prefix' => blocksy_manager()->screen->get_prefix([
					// 'allowed_prefixes' => ['blog'],
					// 'default_prefix' => 'blog'
				])
			]
		);
		
		$card_render = apply_filters(
			'blocksy:posts-listing:cards:custom-output',
			null,
			$args['prefix']
		);

		$entry_open = '<article ';
		$entry_open .= blocksy_attr_to_html($args['item_attributes']) . ' ';
		$entry_open .= blocksy_schema_org_definitions('creative_work:related_posts');
		$entry_open .= '>';

		$entry_close = '</article>';

		if ($card_render) {
			echo $entry_open;
			do_action('blocksy:single:related_posts:card:top');
			echo $card_render['output'];
			do_action('blocksy:single:related_posts:card:bottom');
			echo $entry_close;

			return;
		}

		$related_order = apply_filters(
			'blocksy:posts-listing:related-order',
			blocksy_get_theme_mod(
				$args['prefix'] . '_related_order',
				apply_filters('blocksy:posts-listing:related-order:default', [
					[
						'id' => 'featured_image',
						'enabled' => true,
					],

					[
						'id' => 'title',
						'enabled' => true,
					],

					[
						'id' => 'post_meta',
						'enabled' => true,
						'meta_elements' => blocksy_post_meta_defaults([
							[
								'id' => 'post_date',
								'enabled' => true,
							],
			
							[
								'id' => 'comments',
								'enabled' => true,
							],
						]),
						'__id' => 'meta_1'
					],
				], $args['prefix'])
			)
		);

		$featured_image_settings = null;
		$title_settings = null;
		$post_meta_settings = null;

		$last_enabled_component = null;
		$enabled_components_count = 0;

		if (! $related_order) {
			$related_order = [];
		}

		$has_title_link = false;

		foreach (array_reverse($related_order) as $index => $value) {
			if ($value['id'] === 'featured_image') {
				$featured_image_settings = $value;
			}

			if ($value['id'] === 'post_meta') {
				$post_meta_settings = $value;
			}

			if ($value['id'] === 'title') {
				$title_settings = $value;

				if ($value['enabled']) {
					$has_title_link = blocksy_default_akg('has_link', $value, 'yes') === 'yes';
				}
			}
		}

		foreach (array_reverse($related_order) as $index => $value) {
			if ($value['enabled']) {
				if (! $last_enabled_component) {
					if (! isset($value['__id'])) {
						$id = blocksy_rand_md5();

						$related_order[count($related_order) - 1 - $index]['__id'] = $id;
						$value['__id'] = $id;
					}

					$last_enabled_component = $value['id'] . $value['__id'];
				}

				$enabled_components_count++;
			}
		}

		$image_hover_effect = blocksy_default_akg('image_hover_effect', $featured_image_settings, 'none');
		$featured_image_size = blocksy_default_akg('image_size', $featured_image_settings, 'medium_large');
		$featured_image_has_link = blocksy_default_akg('has_link', $featured_image_settings, 'yes') === 'yes';

		$featured_image_args = [
			'class' => trim($image_hover_effect !== 'none' ? 'has-hover-effect' : ''),
			'attachment_id' => apply_filters(
				'blocksy:related:render-card-layers:featured_image:attachment_id',
				get_post_thumbnail_id()
			),
			'post_id' => get_the_ID(),
			'ratio' => blocksy_default_akg('thumb_ratio', $featured_image_settings, '16/9'),
			'tag_name' => $featured_image_has_link ? 'a' : 'figure',
			'size' => $featured_image_size,
			'html_atts' => $featured_image_has_link ? [
				'href' => esc_url(get_permalink()),
				'aria-label' => wp_strip_all_tags(get_the_title()),
			] : [],
			'lazyload' => blocksy_get_theme_mod(
				'has_lazy_load_relateds_image',
				'yes'
			) === 'yes',
			'display_video' => blocksy_default_akg('has_related_video_thumbnail', $featured_image_settings, 'no') === 'yes'
		];

		if (
			$has_title_link
			&&
			$featured_image_has_link
		) {
			$featured_image_args['html_atts']['tabindex'] = '-1';
		}

		$outputs = null;

		$card_content_classes = get_post_class('entry-card');

		echo $entry_open;

		do_action('blocksy:single:related_posts:card:top');
		
		foreach ($related_order as $single_component) {
			if (! $single_component['enabled']) {
				continue;
			}

			$output = '';
			
			if ('post_meta' === $single_component['id']) {
				$post_meta_default = blocksy_post_meta_defaults([
					[
						'id' => 'post_date',
						'enabled' => true,
					],
	
					[
						'id' => 'comments',
						'enabled' => true,
					],
				]);
				
				$total_metas = [];

				foreach ($related_order as $nested_single_component) {
					if ($nested_single_component['id'] === 'post_meta') {
						$total_metas[] = $nested_single_component;
					}
				}
				
				$has_term_accent_color = 'yes';

				foreach (blocksy_akg('meta_elements', $single_component, $post_meta_default) as $meta_element) {
					if ($meta_element['id'] === 'categories') {
						$has_term_accent_color = blocksy_akg('has_term_accent_color', $meta_element, 'yes');
					}
				}

				$output = blocksy_post_meta(
					blocksy_akg(
						'meta_elements',
						$single_component,
						$post_meta_default
					),
					[
						'meta_type' => blocksy_akg('meta_type', $single_component, 'simple'),
						'meta_divider' => blocksy_akg('meta_divider', $single_component, 'slash'),
						'has_term_class' => $has_term_accent_color === 'yes',
						'attr' => [
							'data-id' => substr($single_component['__id'], 0, 6)
						]
					]
				);
			}

			if (! $outputs) {
				$featured_image_output = '';

				if (
					get_the_post_thumbnail($featured_image_args['attachment_id'])
					||
					wp_get_attachment_image_url($featured_image_args['attachment_id'])
				) {
					$featured_image_output = blocksy_media($featured_image_args);
				}

				$outputs = apply_filters('blocksy:related:render-card-layers', [
					'title' => blocksy_entry_title(
						blocksy_default_akg('heading_tag', $title_settings, 'h4'),
						blocksy_default_akg('has_link', $title_settings, 'yes') === 'yes',
						['related-entry-title']
					),

					'featured_image' => apply_filters(
						'post_thumbnail_html',
						$featured_image_output,
						get_the_ID(),
						$featured_image_args['attachment_id'],
						$featured_image_args['size'],
						''
					),
				], $args['prefix'], $featured_image_args);
			}

			if (isset($outputs[$single_component['id']])) {
				$output = $outputs[$single_component['id']];
			}

			$output = apply_filters(
				'blocksy:related:render-card-layer',
				$output,
				$single_component
			);

			if (! isset($single_component['__id'])) {
				$single_component['__id'] = '';
			}

			/**
			 * Note to code reviewers: This line doesn't need to be escaped.
			 * Variabile $output used here escapes the value properly.
			 */
			echo $output;
		}

		do_action('blocksy:single:related_posts:card:bottom');

		echo $entry_close;
	}
}


