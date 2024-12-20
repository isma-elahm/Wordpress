<?php

add_action('wp_ajax_blocksy_get_trending_posts', 'blc_get_trending_posts');
add_action('wp_ajax_nopriv_blocksy_get_trending_posts', 'blc_get_trending_posts');

if (! function_exists('blc_get_trending_posts')) {
	function blc_get_trending_posts() {
		if (! isset($_REQUEST['page'])) {
			wp_send_json_error();
		}

		$page = intval(sanitize_text_field($_REQUEST['page']));

		if (! $page) {
			wp_send_json_error();
		}

		wp_send_json_success([
			'posts' => blc_get_trending_posts_value([
				'paged' => $page
			])
		]);
	}
}

if (! function_exists('blc_get_trending_posts_value')) {
	function blc_get_trending_posts_value($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'paged' => 1
			]
		);

		$date_query = [];

		$date_filter = blocksy_get_theme_mod('trending_block_filter', 'all_time');

		if ($date_filter && 'all_time' !== $date_filter) {
			$days = [
				'last_24_hours' => 1,
				'last_7_days' => 7,
				'last_month' => 30
			][$date_filter];

			if (! $days) {
				$days = 30;
			}

			$time = time() - (intval($days) * 24 * 60 * 60);

			$date_query = array(
				'after' => [
					'year' => date('Y', $time),
					'month' => date('n', $time),
					'days' => date('j', $time),
				],
				'before' => [
					'year' => date('Y'),
					'month' => date('n'),
					'days' => date('j'),
				],
				'inclusive' => true,
			);
		}

		$post_type = blocksy_get_theme_mod('trending_block_post_type', 'post');

		if (
			(
				$post_type === 'product'
				&&
				! class_exists('WooCommerce')
			)
			||
			! post_type_exists($post_type)
		) {
			$post_type = 'post';
		}

		$source = blocksy_get_theme_mod('trending_block_post_source', 'categories');

		$query_args = [
			'post_type' => $post_type,
			'order' => 'DESC',
			'posts_per_page' => 4,
			'orderby' => 'comment_count',
			'paged' => $args['paged'],
			'ignore_sticky_posts' => true,
			'post_status' => 'publish'
		];

		if ($post_type === 'product') {
			if (get_option('woocommerce_hide_out_of_stock_items') === 'yes') {
				$query_args['meta_query'] = [
					[
						'key' => '_stock_status',
						'value' => 'instock'
					],
					[
						'key' => '_backorders',
						'value' => 'no'
					]
				];
			}

			$trending_product_type = blocksy_get_theme_mod('trending_block_product_type', 'defualt');

			if ($trending_product_type === 'sale') {
				$query_args['post__in'] = wc_get_product_ids_on_sale();
			}

			if ($trending_product_type === 'best') {
				$query_args['meta_key'] = 'total_sales';
			}

			if ($trending_product_type === 'rating') {
				$query_args['meta_key'] = '_wc_average_rating';
				$query_args['orderby'] = 'meta_value_num';
				$query_args['order'] = 'DESC';
			}
		}

		if ($source === 'categories') {
			$query_args['date_query'] = $date_query;
			$cat_option_id = 'trending_block_category';

			if ($post_type !== 'post') {
				$cat_option_id = 'trending_block_' . $post_type . '_taxonomy';
			}

			$cat_id = blocksy_get_theme_mod($cat_option_id, 'all_categories');
			$cat_id = (empty($cat_id) || 'all_categories' === $cat_id) ? '' : $cat_id;

			if (! empty($cat_id)) {
				$terms = get_terms(['include' => $cat_id]);

				if (! empty($terms)) {
					$query_args['tax_query'] = [
						[
							'taxonomy' => $terms[0]->taxonomy,
							'field' => 'term_id',
							'terms' => [$cat_id]
						]
					];
				}
			}
		}

		if ($source === 'custom') {
			$post_id = blocksy_get_theme_mod('trending_block_post_id', '');

			$query_args['orderby'] = 'post__in';
			$query_args['post__in'] = ['__INEXISTING__'];

			if (! empty(trim($post_id))) {
				$query_args['post__in'] = explode(',', str_replace(' ', '', trim(
					$post_id
				)));
			}
		}

		$query = new WP_Query(apply_filters(
			'blocksy:trending-posts:query-args',
			$query_args
		));

		if (! $query->have_posts()) {
			return [
				'posts' => [],
				'is_last_page' => false
			];
		}

		$result = [];

		while ($query->have_posts()) {
			$query->the_post();

			$post_categories = get_the_category();

			$individual_entry = [
				'id' => get_the_ID(),
				'attachment_id' => get_post_thumbnail_id(),
				'title' => get_the_title(),
				'url' => get_permalink(),
				'image' => '',
				'taxonomy' => '',
				'price' => ''
			];

			if (get_post_thumbnail_id()) {
				$individual_entry['image'] = blocksy_media(
					[
						'attachment_id' => get_post_thumbnail_id(),
						'size' => blocksy_get_theme_mod(
							'trending_block_thumbnails_size',
							'thumbnail'
						),
						'ratio' => '1/1',
						'tag_name' => 'a',
						'html_atts' => [
							'href' => esc_url(get_permalink()),
						],
					]
				);
			}

			$show_taxonomy = blocksy_get_theme_mod('trending_block_show_taxonomy', 'no') === 'yes';
			$taxonomy_style = blocksy_get_theme_mod('trending_block_taxonomy_style', 'simple');
			$taxonomy_opt = blocksy_get_taxonomies_for_cpt(
				$post_type,
				['return_empty' => true]
			);

			$taxonomies_to_render = [];

			if (
				$show_taxonomy
				&&
				! empty($taxonomy_opt)
			) {
				$taxonomy_option_id = 'trending_block_show_' . $post_type . '_taxonomy';

				$taxonomy_to_show = blocksy_get_theme_mod(
					$taxonomy_option_id,
					$post_type === 'product' ? 'product_cat' : array_keys($taxonomy_opt)[0]
				);

				$taxonomy_values = get_the_terms(get_the_ID(), $taxonomy_to_show);

				if (
					$taxonomy_values
					&&
					is_array($taxonomy_values)
				) {
					foreach ($taxonomy_values as $tax) {						
						$taxonomies_to_render[] = blocksy_html_tag(
							'a',
							[
								'href' => get_term_link($tax),
								'class' => 'ct-post-taxonomy',
							],
							$tax->name
						);
					}
	
					if (! empty($taxonomies_to_render)) {

						$divider = '';

						if ($taxonomy_style === 'simple') {
							$divider = ', ';
						}

						if ($taxonomy_style === 'underline') {
							$divider = ' / ';
						}

						$individual_entry['taxonomy'] = blocksy_html_tag(
							'ul',
							[
								'class' => 'entry-meta'
							],
							blocksy_html_tag(
								'li',
								[
									'class' => 'meta-categories',
									'data-type' => $taxonomy_style
								],
								implode($divider, $taxonomies_to_render)
							)
						);
					}
				}
			}

			$trending_block_show_price = blocksy_get_theme_mod('trending_block_show_price', 'no') === 'yes';

			if (
				$trending_block_show_price
				&&
				$post_type === 'product'
			) {
				$product = wc_get_product(get_the_ID());

				$individual_entry['price'] = blocksy_html_tag(
					'span',
					[
						'class' => 'price'
					],
					$product->get_price_html()
				);
			}

			$result[] = $individual_entry;
		}

		$is_last = intval($query->max_num_pages) === intval($args['paged']);

		wp_reset_postdata();

		return [
			'posts' => $result,
			'is_last_page' => $is_last
		];
	}
}

if (! function_exists('blc_get_trending_block')) {
function blc_get_trending_block($result = null) {
	if (! $result) {
		$result = blc_get_trending_posts_value();
	}


	if (empty($result['posts'])) {
		return '';
	}

	ob_start();

	$data_page = 'data-page="1"';

	if ($result['is_last_page']) {
		$data_page = '';
	}

	$class = 'ct-trending-block';

	$class .= ' ' . blocksy_visibility_classes(
		blocksy_get_theme_mod('trending_block_visibility', [
			'desktop' => true,
			'tablet' => true,
			'mobile' => false,
		])
	);

	$attr = [
		'class' => $class
	];

	if (is_customize_preview()) {
		$attr['data-shortcut'] = 'border';
		$attr['data-shortcut-location'] = 'trending_posts_ext';
	}

	$label_tag = blocksy_get_theme_mod('trending_block_label_tag', 'h3');

	$trending_label = blocksy_get_theme_mod(
		'trending_block_label',
		__('Trending now', 'blocksy-companion')
	);

	$icon = '<svg width="13" height="13" viewBox="0 0 13 13" fill="currentColor"><path d="M13 5.8V9c0 .4-.2.6-.5.6s-.5-.2-.5-.5V7.2l-4.3 4.2c-.2.2-.6.2-.8 0L4.6 9.1.9 12.8c-.1.1-.2.2-.4.2s-.3-.1-.4-.2c-.2-.2-.2-.6 0-.8l4.1-4.1c.2-.2.6-.2.8 0l2.3 2.3 3.8-3.8H9.2c-.3 0-.5-.2-.5-.5s.2-.5.5-.5h3.4c.2 0 .3.1.4.2v.2z"/></svg>';

	if (function_exists('blc_get_icon')) {
		$icon_source = blocksy_get_theme_mod('trending_block_icon_source', 'default');

		if ($icon_source === 'custom') {
			$icon = blc_get_icon([
				'icon_descriptor' => blocksy_get_theme_mod('trending_block_custom_icon', [
					'icon' => 'fas fa-fire',
				]),
				'icon_container' => false,
				'icon_html_atts' => [
					'width' => '13',
					'height' => '13',
					'fill' => 'currentColor'
				]
			]);
		}
	}

	?>

	<section <?php echo blocksy_attr_to_html($attr) ?>>
		<div class="ct-container" <?php echo $data_page ?>>

			<<?php echo $label_tag ?> class="ct-module-title">
				<?php 
					echo $trending_label;

					/**
					 * Note to code reviewers: This line doesn't need to be escaped.
					 * The value used here escapes the value properly.
					 * It contains an inline SVG, which is safe.
					 */
					echo $icon;
				?>
				
				<?php if (! $result['is_last_page']) { ?>
					<span class="ct-slider-arrows">
						<span class="ct-arrow-prev">
							<svg width="8" height="8" fill="currentColor" viewBox="0 0 8 8">
								<path d="M5.05555,8L1.05555,4,5.05555,0l.58667,1.12-2.88,2.88,2.88,2.88-.58667,1.12Z"/>
							</svg>
						</span>

						<span class="ct-arrow-next">
							<svg width="8" height="8" fill="currentColor" viewBox="0 0 8 8">
								<path d="M2.35778,6.88l2.88-2.88L2.35778,1.12,2.94445,0l4,4-4,4-.58667-1.12Z"/>
							</svg>
						</span>
					</span>
				<?php } ?>
			</<?php echo $label_tag ?>>

			<?php
				foreach ($result['posts'] as $post) {
					echo blocksy_html_tag(
						'div',
						[
							'class' => 'ct-trending-block-item'
						],
						$post['image'] .
						blocksy_html_tag(
							'div',
							[
								'class' => 'ct-trending-block-item-content'
							],
							$post['taxonomy'] .
							blocksy_html_tag(
								'a',
								[
									'href' => $post['url'],
									'class' => 'ct-post-title',
								],
								$post['title']
							) .
							$post['price']
						)
					);
				}
			?>

		</div>
	</section>

	<?php

	return ob_get_clean();
}
}
