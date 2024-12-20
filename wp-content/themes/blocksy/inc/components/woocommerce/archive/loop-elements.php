<?php

add_action(
	'wp',
	function () {
		if (blocksy_get_theme_mod('has_shop_sort', 'yes') !== 'yes') {
			remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
		}

		if (blocksy_get_theme_mod('has_shop_results_count', 'yes') !== 'yes') {
			remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
		}
	},
	5
);

add_action(
	'woocommerce_before_template_part',
	function ($template_name, $template_path, $located, $args) {
		if (
			$template_name === 'loop/result-count.php'
			||
			$template_name === 'loop/orderby.php'
		) {
			ob_start();
		}
	},
	1,
	4
);

add_action(
	'woocommerce_after_template_part',
	function ($template_name, $template_path, $located, $args) {
		if ($template_name === 'loop/result-count.php') {
			$result = ob_get_clean();

			$reader = new \WP_HTML_Tag_Processor($result);

			if (
				$reader->next_tag([
					'tag_name' => 'p',
					'class_name' => 'woocommerce-result-count',
				])
			) {
				$classes = blocksy_visibility_classes(
					blocksy_get_theme_mod(
						'shop_results_count_visibility',
						[
							'desktop' => true,
							'tablet' => true,
							'mobile' => false,
						]
					),
					[
						'output' => 'array',
					]
				);

				foreach ($classes as $class) {
					$reader->add_class($class);
				}

				$result = $reader->get_updated_html();
			}

			echo $result;
		}

		if ($template_name === 'loop/orderby.php') {
			$result = ob_get_clean();

			$reader = new \WP_HTML_Tag_Processor($result);

			if (
				$reader->next_tag([
					'tag_name' => 'form',
					'class_name' => 'woocommerce-ordering',
				])
				&&
				doing_action('woocommerce_before_shop_loop')
			) {
				$classes = blocksy_visibility_classes(
					blocksy_get_theme_mod(
						'shop_sort_visibility',
						[
							'desktop' => true,
							'tablet' => true,
							'mobile' => true
						]
					),
					[
						'output' => 'array',
					]
				);

				foreach ($classes as $class) {
					$reader->add_class($class);
				}

				$result = $reader->get_updated_html();

				$svg = '<svg width="14px" height="14px" fill="currentColor" viewBox="0 0 24 24" class="ct-sort-icon ct-hidden-lg ct-hidden-md"><path d="M10.434 5.966A.933.933 0 0 1 9.24 7.399L6.318 4.966V19.46a.933.933 0 1 1-1.866 0V4.966L1.53 7.4a.93.93 0 0 1-1.314-.12.933.933 0 0 1 .12-1.313l4.452-3.707c.027-.023.06-.03.09-.048.038-.026.072-.055.114-.074.025-.012.051-.02.078-.03.046-.017.092-.025.14-.034.045-.009.088-.02.134-.022.014 0 .026-.008.04-.008.015 0 .027.008.042.008.046.002.09.013.135.022.047.01.093.017.139.034.026.01.052.018.078.03.042.019.076.048.115.074.029.019.062.026.089.048l4.452 3.707Zm13.35 10.755a.933.933 0 0 0-1.314-.12l-2.922 2.433V4.54a.933.933 0 1 0-1.866 0v14.493L14.76 16.6a.933.933 0 1 0-1.194 1.433l4.452 3.707c.044.037.096.055.144.082.027.015.049.04.077.052l.038.014c.11.042.223.068.338.068a.929.929 0 0 0 .338-.068l.038-.014c.029-.013.05-.037.078-.052.048-.027.099-.045.143-.082l4.452-3.707a.933.933 0 0 0 .12-1.313Z"/></svg>
				';

				$result = str_replace(
					'</form>',
					$svg . '</form>',
					$result
				);
			}

			echo $result;
		}
	},
	1,
	4
);
