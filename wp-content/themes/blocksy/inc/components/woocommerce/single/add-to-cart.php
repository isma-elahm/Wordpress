<?php

namespace Blocksy;

class WooCommerceAddToCart {
	use WordPressActionsManager;

	private $finalize_action_name = '';

	private $handled_product_ids = [];

	private $actions = [
		[
			'action' => 'woocommerce_before_add_to_cart_form',
			'priority' => 10
		],

		[
			'action' => 'woocommerce_before_add_to_cart_quantity',
			'priority' => PHP_INT_MAX
		],

		[
			'action' => 'woocommerce_before_add_to_cart_button',
			'priority' => PHP_INT_MAX
		],

		[
			'action' => 'woocommerce_after_add_to_cart_button',
			'priority' => 100
		],

		[
			'action' => 'woocommerce_post_class',
			'priority' => 10
		]
	];

	public function __construct() {
		$this->attach_hooks([
			'exclude' => [
				'woocommerce_after_add_to_cart_button'
			]
		]);

		if (isset($_REQUEST['blocksy_add_to_cart'])) {
			add_filter(
				'woocommerce_add_to_cart_redirect',
				'__return_false'
			);
		}

		// Make sure we process response exactly same way as Woo does. In the
		// exact point of time.
		//
		// https://github.com/woocommerce/woocommerce/blob/9f26128dc2a223d5b9e5482e6a41b5052bfc91c4/plugins/woocommerce/includes/class-wc-ajax.php#L30
		add_action('template_redirect', [$this, 'do_wc_ajax'], 0);
	}

	public function do_wc_ajax() {
		if (! isset($_REQUEST['blocksy_add_to_cart'])) {
			return;
		}

		ob_start();
		wc_print_notices();
		$notices = ob_get_clean();

		ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		$data = [
			'notices' => $notices,
			'fragments' => apply_filters(
				'woocommerce_add_to_cart_fragments',
				[
					'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
				]
			),
			'cart_hash' => WC()->cart->get_cart_hash()
		];

		wp_send_json_success($data);
	}

	private function product_was_handled($product) {
		if (! $product instanceof \WC_Product) {
			return false;
		}

		return in_array($product->get_id(), $this->handled_product_ids);
	}

	private function output_cart_action_open() {
		if (
			(
				blocksy_manager()->screen->is_product()
				||
				wp_doing_ajax()
			)
			&&
			! blocksy_manager()->screen->uses_woo_default_template()
		) {
			return;
		}

		$attr = apply_filters('blocksy:woocommerce:cart-actions:attr', [
			'class' => 'ct-cart-actions'
		]);

		echo '<div ' . blocksy_attr_to_html($attr) . '>';

		$this->attach_hooks([
			'only' => [
				'woocommerce_after_add_to_cart_button'
			]
		]);
	}

	public function finalize_add_to_cart() {
		global $product;

		if ($this->product_was_handled($product)) {
			return;
		}

		if (! $product) {
			return;
		}

		// On single product pages we know for sure that there's only one
		// product that needs handling. On other pages or during AJAX requests,
		// we need to make sure that we don't handle the same product twice.
		if (blocksy_manager()->screen->is_product()) {
			$this->detach_hooks();
		} else {
			$this->handled_product_ids[] = $product->get_id();
		}

		remove_action(
			$this->finalize_action_name,
			[$this, 'finalize_add_to_cart'],
			50
		);
	}

	public function woocommerce_before_add_to_cart_form() {
		global $product;
		global $root_product;

		if ($this->product_was_handled($product)) {
			return;
		}

		$root_product = $product;
	}

	public function woocommerce_before_add_to_cart_quantity() {
		global $product;
		global $root_product;

		if ($this->product_was_handled($product)) {
			return;
		}

		if (! $root_product) {
			return;
		}

		if (
			! $root_product->is_type('simple')
			&&
			! $root_product->is_type('variation')
			&&
			! $root_product->is_type('variable')
			&&
			! $this->check_product_type($root_product)
		) {
			return;
		}

		$this->output_cart_action_open();
	}

	public function woocommerce_before_add_to_cart_button() {
		global $product;
		global $root_product;

		if ($this->product_was_handled($product)) {
			return;
		}

		if (! $root_product) {
			return;
		}

		if (
			! $root_product->is_type('grouped')
			&&
			! $root_product->is_type('external')
		) {
			return;
		}

		$this->output_cart_action_open();
	}

	public function woocommerce_after_add_to_cart_button() {
		global $product;

		if ($this->product_was_handled($product)) {
			return;
		}

		if (! $product) {
			return;
		}

		if (
			! $product->is_type('simple')
			&&
			! $product->is_type('variable')
			&&
			! $product->is_type('grouped')
			&&
			! $product->is_type('external')
			&&
			! $this->check_product_type($product)
		) {
			return;
		}

		if (
			(
				$product->is_type('simple')
				||
				$product->is_type('variable')
				||
				$this->check_product_type($product)
			)
			&&
			! did_action('woocommerce_before_add_to_cart_quantity')
		) {
			return;
		}

		echo '</div>';

	}

	public function woocommerce_post_class($classes) {
		global $product;
		global $woocommerce_loop;

		if ($this->product_was_handled($product)) {
			return $classes;
		}

		$classes = apply_filters(
			'blocksy:woocommerce:single-product:post-class',
			$classes
		);

		$default_product_layout = blocksy_get_woo_single_layout_defaults();

		$layout = blocksy_get_theme_mod(
			'woo_single_layout',
			blocksy_get_woo_single_layout_defaults()
		);

		if (
			(
				function_exists('blocksy_has_product_specific_layer')
				&&
				! blocksy_has_product_specific_layer('product_add_to_cart')
			)
			||
			! $product
			||
			$product->is_type('external')
			||
			(
				! blocksy_manager()->screen->is_product()
				&&
				! wp_doing_ajax()
			)
		) {
			return $classes;
		}

		$has_ajax_add_to_cart = blocksy_get_theme_mod(
			'has_ajax_add_to_cart',
			'yes'
		);

		if (
			$has_ajax_add_to_cart === 'yes'
			&&
			get_option('woocommerce_cart_redirect_after_add', 'no') === 'no'
		) {
			$classes[] = 'ct-ajax-add-to-cart';
		}

		// https://github.com/woocommerce/woocommerce/blob/701d9341dde6fa8861684fb161cdca9ec94a7a4d/plugins/woocommerce/includes/wc-template-functions.php#L1784
		$this->finalize_action_name = 'woocommerce_' . $product->get_type() . '_add_to_cart';

		add_action(
			$this->finalize_action_name,
			[$this, 'finalize_add_to_cart'],
			50
		);

		return $classes;
	}

	public function check_product_type($product) {
		$allowed_custom_product_types = [
			'subscription',
			'variable-subscription',
			'woosb'
		];

		return in_array($product->get_type(), $allowed_custom_product_types);
	}
}

