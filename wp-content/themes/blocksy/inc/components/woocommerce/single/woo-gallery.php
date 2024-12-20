<?php

add_action(
	'woocommerce_before_template_part',
	function ($template_name, $template_path, $located, $args) {
		if ($template_name !== 'single-product/product-image.php') {
			return;
		}

		if (! blocksy_woocommerce_has_flexy_view()) {
			return;
		}

		echo blocksy_render_view(dirname(__FILE__) . '/woo-gallery-template.php');

		ob_start();
	},
	4, 4
);

add_action(
	'woocommerce_after_template_part',
	function ($template_name, $template_path, $located, $args) {
		if ($template_name !== 'single-product/product-image.php') {
			return;
		}

		if (! blocksy_woocommerce_has_flexy_view()) {
			return;
		}

		ob_get_clean();
	},
	4, 4
);


add_filter(
	'blocksy:woocommerce:single-product:post-class',
	function($classes) {
		if (! blocksy_manager()->screen->is_product()) {
			return $classes;
		}

		global $blocksy_is_quick_view;
		global $product;

		if (
			! $blocksy_is_quick_view
			&&
			// Integration with Custom Product Boxes plugin
			$product->get_type() !== 'wdm_bundle_product'
		) {
			$classes[] = 'ct-default-gallery';
		}

		return $classes;
	}
);

add_filter(
	'woocommerce_post_class',
	'blocksy_woo_single_post_class',
	999,
	2
);

function blocksy_woo_single_post_class($classes, $product) {
	if (! blocksy_manager()->screen->is_product()) {
		return $classes;
	}

	$product_view_type = blocksy_get_product_view_type();

	if (blocksy_woocommerce_has_flexy_view()) {
		$has_gallery = count($product->get_gallery_image_ids()) > 0;

		if ($product->get_type() === 'variable') {
			$maybe_current_variation = blocksy_manager()
				->woocommerce
				->retrieve_product_default_variation($product);

			if ($maybe_current_variation) {
				$variation_values = blocksy_get_post_options(
					blocksy_translate_post_id(
						$maybe_current_variation->get_id(),
						[
							'use_wpml_default_language_woo' => true
						]
					)
				);

				$gallery_source = blocksy_akg(
					'gallery_source',
					$variation_values,
					'default'
				);

				if ($gallery_source !== 'default') {
					$has_gallery = count(blocksy_akg(
						'images',
						$variation_values,
						[]
					)) > 0;
				}
			}
		}

		if ($has_gallery) {
			if (
				blocksy_get_theme_mod('gallery_style', 'horizontal') === 'vertical'
				&&
				$product_view_type === 'default-gallery'
			) {
				$classes[] = 'thumbs-left';
			} else {
				$classes[] = 'thumbs-bottom';
			}
		}
	}

	if (
		$product_view_type === 'default-gallery'
		||
		$product_view_type === 'stacked-gallery'
	) {
		if (blocksy_get_theme_mod('has_product_sticky_gallery', 'no') === 'yes') {
			$classes[] = 'sticky-gallery';
		}

		if (blocksy_get_theme_mod('has_product_sticky_summary', 'no') === 'yes') {
			$classes[] = 'sticky-summary';
		}
	}

	return $classes;
}

function blocksy_get_product_view_type() {
	return apply_filters(
		'blocksy:woocommerce:product-single:view-type',
		'default-gallery'
	);
}

// Only for backwards compatibility with Companion <= 2.0.73
function blocksy_retrieve_product_default_variation($product, $object = true) {
	return blocksy_manager()
		->woocommerce
		->retrieve_product_default_variation($product, $object);
}
