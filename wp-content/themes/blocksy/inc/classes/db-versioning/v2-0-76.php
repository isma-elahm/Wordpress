<?php

namespace Blocksy\DbVersioning;

class V2076 {
	public function migrate() {
		$prefixes = [];

		foreach (blocksy_manager()->screen->get_single_prefixes() as $prefix) {
			$prefixes[] = $prefix;
		}

		foreach ($prefixes as $prefix) {
			$related_order = get_theme_mod(
				$prefix . '_related_order',
				'__empty__'
			);

			if ($related_order !== '__empty__') {
				continue;
			}

			$new_option_value = [];

			$ratio = get_theme_mod($prefix . '_related_featured_image_ratio', '16/9');
			$image_size = get_theme_mod($prefix . '_related_featured_image_size', 'medium_large');
			$has_link = get_theme_mod($prefix . '_related_featured_image_has_link', 'yes');
			$has_related_featured_image = get_theme_mod($prefix . '_has_related_featured_image', 'yes');

			$new_option_value[] = [
				'id' => 'featured_image',
				'thumb_ratio' => $ratio,
				'image_size' => $image_size,
				'enabled' => $has_related_featured_image === 'yes',
				'has_link' => $has_link
			];

			$heading_tag = get_theme_mod($prefix . '_related_posts_title_tag', 'h4');
			$heading_has_link = get_theme_mod($prefix . '_related_featured_title_has_link', 'yes');

			$new_option_value[] = [
				'id' => 'title',
				'heading_tag' => $heading_tag,
				'enabled' => true,
				'has_link' => $heading_has_link,
			];

			$meta_elements = get_theme_mod(
				$prefix . '_related_single_meta_elements',
				blocksy_post_meta_defaults([
					[
						'id' => 'post_date',
						'enabled' => true,
					],

					[
						'id' => 'comments',
						'enabled' => true,
					],
				])
			);

			$new_option_value[] = [
				'id' => 'post_meta',
				'enabled' => true,
				'meta_elements' => $meta_elements,
				'meta_type' => 'simple',
				'meta_divider' => 'slash',
			];

			set_theme_mod($prefix . '_related_order', $new_option_value);

			remove_theme_mod($prefix . '_has_related_featured_image');
			remove_theme_mod($prefix . '_related_featured_image_ratio');
			remove_theme_mod($prefix . '_related_featured_image_size');
			remove_theme_mod($prefix . '_related_featured_image_has_link');
			remove_theme_mod($prefix . '_related_posts_title_tag');
			remove_theme_mod($prefix . '_related_featured_title_has_link');
			remove_theme_mod($prefix . '_related_single_meta_elements');
		}
	}
}
