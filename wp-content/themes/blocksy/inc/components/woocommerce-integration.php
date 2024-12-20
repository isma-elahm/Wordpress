<?php

namespace Blocksy;

require get_template_directory() . '/inc/components/woocommerce/general.php';

require get_template_directory() . '/inc/components/woocommerce/common/layer-defaults.php';
require get_template_directory() . '/inc/components/woocommerce/common/rest-api.php';
require get_template_directory() . '/inc/components/woocommerce/common/cart.php';
require get_template_directory() . '/inc/components/woocommerce/common/account.php';
require get_template_directory() . '/inc/components/woocommerce/common/store-notice.php';
require get_template_directory() . '/inc/components/woocommerce/common/mini-cart.php';
require get_template_directory() . '/inc/components/woocommerce/common/sale-flash.php';
require get_template_directory() . '/inc/components/woocommerce/common/stock-badge.php';

require get_template_directory() . '/inc/components/woocommerce/archive/helpers.php';
require get_template_directory() . '/inc/components/woocommerce/archive/index.php';
require get_template_directory() . '/inc/components/woocommerce/archive/product-card.php';
require get_template_directory() . '/inc/components/woocommerce/archive/loop.php';
require get_template_directory() . '/inc/components/woocommerce/archive/loop-elements.php';
require get_template_directory() . '/inc/components/woocommerce/archive/pagination.php';

require get_template_directory() . '/inc/components/woocommerce/single/helpers.php';
require get_template_directory() . '/inc/components/woocommerce/single/review-form.php';
require get_template_directory() . '/inc/components/woocommerce/single/single-modifications.php';
require get_template_directory() . '/inc/components/woocommerce/single/add-to-cart.php';
require get_template_directory() . '/inc/components/woocommerce/single/woo-gallery.php';
require get_template_directory() . '/inc/components/woocommerce/single/tabs.php';

// if (class_exists('WC_Additional_Variation_Images_Frontend')) {
	require get_template_directory() . '/inc/components/woocommerce/integrations/woocommerce-additional-variation-images.php';
// }

if (class_exists('Custom_Related_Products')) {
	require get_template_directory() . '/inc/components/woocommerce/integrations/wt-woocommerce-related-products.php';

}

add_filter(
	'blocksy_theme_autoloader_classes_map',
	function ($classes) {
		$prefix = 'inc/components/woocommerce/';

		$classes['WooCommerceBoot'] = $prefix . 'boot.php';
		$classes['WooCommerceImageSizes'] = $prefix . 'common/image-sizes.php';

		$classes['WooCommerceSingle'] = $prefix . 'single/single.php';
		$classes['WooCommerceAddToCart'] = $prefix . 'single/add-to-cart.php';
		$classes['SingleProductAdditionalActions'] = $prefix . 'single/additional-actions-layer.php';

		$classes['WooCommerceCheckout'] = $prefix . 'common/checkout.php';

		return $classes;
	}
);

class WooCommerce {
	public $single = null;
	public $checkout = null;

	private $default_variation_cache = [];

	public function __construct() {
		new WooCommerceBoot();

		new WooCommerceImageSizes();

		$this->single = new WooCommerceSingle();

		$this->checkout = new WooCommerceCheckout();
	}

	public function retrieve_product_default_variation($product, $object = true) {
		if (isset($this->default_variation_cache[$product->get_id()])) {
			$cached_variation_id = $this->default_variation_cache[$product->get_id()];

			if ($object && $cached_variation_id) {
				return wc_get_product($cached_variation_id);
			}

			return $cached_variation_id;
		}

		$maybe_variation = null;

		$default_attributes = $product->get_default_attributes();
		$variation_attributes = $product->get_variation_attributes();

		if (count($default_attributes) === count($variation_attributes)) {
			$prefixed_slugs = array_map(function($pa_name) {
				return 'attribute_'. sanitize_title($pa_name);
			}, array_keys($default_attributes));

			$default_attributes = array_combine($prefixed_slugs, $default_attributes);

			$maybe_get_variation = (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
				$product,
				$default_attributes
			);

			if ($maybe_get_variation) {
				$maybe_variation = $maybe_get_variation;
			}
		}

		$has_some_matching_get_param = false;

		foreach ($variation_attributes as $attribute_name => $attribute_values) {
			if (isset($_GET['attribute_' . $attribute_name])) {
				$has_some_matching_get_param = true;
				break;
			}
		}

		if ($has_some_matching_get_param) {
			$maybe_get_variation = (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
				$product,
				$_GET
			);

			if ($maybe_get_variation) {
				$maybe_variation = $maybe_get_variation;
			}
		}

		// Persist only ID in the cache, no need to waste memory on full
		// object which are already cached by Woo.
		$this->default_variation_cache[$product->get_id()] = $maybe_variation;

		if ($object && $maybe_variation) {
			return wc_get_product($maybe_variation);
		}

		return $maybe_variation;
	}
}

