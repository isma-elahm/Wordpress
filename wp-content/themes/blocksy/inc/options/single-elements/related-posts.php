<?php

if (! isset($prefix)) {
	$prefix = '';
} else {
	$prefix = $prefix . '_';
}

if (! isset($enabled)) {
	$enabled = 'no';
}

if (! isset($post_type)) {
	$post_type = 'post';
}

$options = [
	$prefix . 'has_related_posts' => [
		'label' => __('Related Posts', 'blocksy'),
		'type' => 'ct-panel',
		'switch' => true,
		'value' => $enabled,
		'sync' => blocksy_sync_whole_page([
			'prefix' => $prefix,
		]),
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					[

						apply_filters(
							'blocksy_customizer_options:single:related:before',
							[],
							$prefix,
							$post_type
						),

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								$prefix . 'related_posts_slideshow' => '!slider',
							],
							'options' => [

								blocksy_rand_md5() => [
									'type' => 'ct-group',
									'label' => __( 'Columns & Posts', 'blocksy' ),
									'attr' => [ 'data-columns' => '2:medium' ],
									'responsive' => true,
									'hasGroupRevertButton' => true,
									'options' => [

										$prefix . 'related_posts_columns' => [
											'label' => false,
											'type' => 'ct-number',
											'value' => [
												'desktop' => 3,
												'tablet' => 2,
												'mobile' => 1,
												'__changed' => ['tablet', 'mobile']
											],
											'min' => 1,
											'max' => 6,
											'design' => 'block',
											'attr' => [ 'data-width' => 'full' ],
											'desc' => __('Number of columns', 'blocksy' ),
											'sync' => 'live',
											'responsive' => true,
											'skipResponsiveControls' => true,
										],

										$prefix . 'related_posts_count' => [
											'label' => false,
											'type' => 'ct-number',
											'value' => 3,
											'min' => 1,
											'max' => 20,
											'design' => 'block',
											'attr' => [ 'data-width' => 'full' ],
											'desc' => __( 'Number of posts', 'blocksy' ),
											'markAsAutoFor' => ['tablet', 'mobile'],
											'sync' => [
												[
													'prefix' => $prefix,
													'selector' => '.ct-related-posts',
													'render' => function () {
														blocksy_related_posts();
													}
												],

												[
													'id' => $prefix . 'related_posts_count_skip',
													'loader_selector' => 'skip',
													'prefix' => $prefix,
													'selector' => '.ct-related-posts',
													'render' => function () {
														blocksy_related_posts();
													}
												]
											]
										],

									],
								],

							]
						],

						blocksy_rand_md5() => [
							'type' => 'ct-divider',
						],

						$prefix . 'related_criteria' => [
							'label' => __( 'Related Criteria', 'blocksy' ),
							'type' => $prefix === 'single_blog_post_' ? 'ct-select' : 'hidden',
							'type' => 'ct-select',
							'value' => array_keys(blocksy_get_taxonomies_for_cpt(
								$post_type
							))[0],
							'view' => 'text',
							'design' => 'inline',
							'choices' => blocksy_ordered_keys(
								blocksy_get_taxonomies_for_cpt($post_type)
							),
							'sync' => [
								'prefix' => $prefix,
								'selector' => '.ct-related-posts',
								'render' => function () {
									blocksy_related_posts();
								}
							]
						],

						$prefix . 'related_sort' => [
							'type' => 'ct-select',
							'label' => __('Sort by', 'blocksy'),
							'value' => 'recent',
							'design' => 'inline',
							'choices' => blocksy_ordered_keys(
								[
									'default' => __('Default', 'blocksy'),
									'recent' => __('Recent', 'blocksy'),
									'commented' => __('Most Commented', 'blocksy'),
									'random' => __('Random', 'blocksy'),
								]
							),
							'sync' => [
								'prefix' => $prefix,
								'selector' => '.ct-related-posts',
								'render' => function () {
									blocksy_related_posts();
								}
							]
						],

						blocksy_rand_md5() => [
							'type' => 'ct-divider',
						],

						$prefix . 'related_label' => [
							'label' => __( 'Module Title', 'blocksy' ),
							'type' => 'text',
							'design' => 'inline',
							'value' => __( 'Related Posts', 'blocksy' ),
							'sync' => 'live'
						],

						$prefix . 'related_label_wrapper' => [
							'label' => __( 'Module Title Tag', 'blocksy' ),
							'type' => 'ct-select',
							'value' => 'h3',
							'view' => 'text',
							'design' => 'inline',
							'choices' => blocksy_ordered_keys(
								[
									'h1' => 'H1',
									'h2' => 'H2',
									'h3' => 'H3',
									'h4' => 'H4',
									'h5' => 'H5',
									'h6' => 'H6',
									'p' => 'p',
									'span' => 'span',
								]
							),
							'sync' => [
								'prefix' => $prefix,
								'selector' => '.ct-related-posts',
								'loader_selector' => '.ct-module-title',
								'render' => function () {
									blocksy_related_posts();
								}
							]
						],

						$prefix . 'related_label_alignment' => [
							'type' => 'ct-radio',
							'label' => __( 'Module Title Alignment', 'blocksy' ),
							'view' => 'text',
							'design' => 'block',
							'divider' => 'top',
							'responsive' => true,
							'attr' => [ 'data-type' => 'alignment' ],
							'setting' => [ 'transport' => 'postMessage' ],
							'value' => 'CT_CSS_SKIP_RULE',
							'choices' => [
								'left' => '',
								'center' => '',
								'right' => '',
							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-divider',
						],

						$prefix . 'related_order' => apply_filters('blocksy:options:posts-listing-related-order', [
							'label' => __('Card Elements', 'blocksy'),
							'type' => 'ct-layers',
							'sync' => [
								blocksy_sync_whole_page([
									'prefix' => $prefix,
									'loader_selector' => '.ct-related-posts article'
								]),

								blocksy_sync_whole_page([
									'id' => $prefix . 'dynamic_data_sync',
									'prefix' => $prefix,
									'loader_selector' => '.ct-related-posts .ct-dynamic-data-layer'
								]),

								[
									'prefix' => $prefix,
									'id' => $prefix . 'related_order_heading_tag',
									'loader_selector' => '.ct-related-posts .related-entry-title',
									'container_inclusive' => false
								],

								[
									'prefix' => $prefix,
									'id' => $prefix . 'related_order_image',
									'loader_selector' => '.ct-related-posts .ct-media-container',
									'container_inclusive' => false
								],

								[
									'prefix' => $prefix,
									'id' => $prefix . 'related_order_skip',
									'loader_selector' => 'skip',
									'container_inclusive' => false
								],

								[
									'prefix' => $prefix,
									'id' => $prefix . 'related_order_meta_first',
									'loader_selector' => '.ct-related-posts .entry-meta:1',
									'container_inclusive' => false
								],

								[
									'prefix' => $prefix,
									'id' => $prefix . 'related_order_meta_second',
									'loader_selector' => '.ct-related-posts .entry-meta:2',
									'container_inclusive' => false
								],
							],

							'value' => [
								[
									'id' => 'featured_image',
									'thumb_ratio' => '16/9',
									'image_size' => 'medium_large',
									'enabled' => true,
									'has_link' => 'yes',
								],

								[
									'id' => 'title',
									'heading_tag' => 'h4',
									'enabled' => true,
									'has_link' => 'yes',
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
									'meta_type' => 'simple',
									'meta_divider' => 'slash',
								],
							],

							'settings' => [

								'featured_image' => [
									'label' => __('Featured Image', 'blocksy'),
									'options' => [
										[

											'thumb_ratio' => [
												'label' => __('Image Ratio', 'blocksy'),
												'type' => 'ct-ratio',
												'view' => 'inline',
												'value' => '16/9',
												'sync' => [
													'id' => $prefix . 'related_order_skip',
												],
											],

											'image_size' => [
												'label' => __('Image Size', 'blocksy'),
												'type' => 'ct-select',
												'value' => 'medium_large',
												'view' => 'text',
												'design' => 'block',
												'sync' => [
													'id' => $prefix . 'related_order_image',
												],
												'choices' => blocksy_ordered_keys(
													blocksy_get_all_image_sizes()
												),
											],

											'image_hover_effect' => [
												'label' => __( 'Hover Effect', 'blocksy' ),
												'type' => 'ct-select',
												'value' => 'none',
												'view' => 'text',
												'design' => 'block',
												'setting' => [ 'transport' => 'postMessage' ],
												'choices' => blocksy_ordered_keys(
													[
														'none' => __( 'None', 'blocksy' ),
														'zoom-in' => __( 'Zoom In', 'blocksy' ),
														'zoom-out' => __( 'Zoom Out', 'blocksy' ),
													]
												),

												'sync' => blocksy_sync_whole_page([
													'prefix' => $prefix,
													'loader_selector' => '.ct-related-posts article'
												]),
											],
										],

										[
											(
												function_exists('blc_fs')
												&&
												blc_fs()->can_use_premium_code()
											) ? [
												'has_related_video_thumbnail' => [
													'label' => __( 'Video Thumbnail', 'blocksy' ),
													'type' => 'ct-switch',
													'value' => 'no',
													'sync' => blocksy_sync_whole_page([
														'prefix' => $prefix,
														'loader_selector' => '.ct-related-posts article'
													]),
												],
											] : []
										],

										'has_link' => [
											'label' => __('Link To Post', 'blocksy'),
											'type' => 'ct-switch',
											'value' => 'yes',
											'sync' => [
												'id' => $prefix . 'related_order_image',
											],
										],

										'spacing' => [
											'label' => __('Bottom Spacing', 'blocksy'),
											'type' => 'ct-slider',
											'min' => 0,
											'max' => 100,
											'value' => 20,
											'responsive' => true,
											'sync' => [
												'id' => $prefix . 'related_order_skip'
											],
										],
									],
								],

								'title' => [
									'label' => __('Title', 'blocksy'),
									'options' => [

										'heading_tag' => [
											'label' => __('Heading Tag', 'blocksy'),
											'type' => 'ct-select',
											'value' => 'h4',
											'view' => 'text',
											'design' => 'block',
											'sync' => [
												'id' => $prefix . 'related_order_heading_tag',
											],
											'choices' => blocksy_ordered_keys(
												[
													'h1' => 'H1',
													'h2' => 'H2',
													'h3' => 'H3',
													'h4' => 'H4',
													'h5' => 'H5',
													'h6' => 'H6',
													'p' => 'p',
													'span' => 'span',
												]
											),
										],

										'has_link' => [
											'label' => __('Link To Post', 'blocksy'),
											'type' => 'ct-switch',
											'value' => 'yes',
											'sync' => [
												'id' => $prefix . 'related_order_heading_tag',
											],
										],

										'spacing' => [
											'label' => __('Bottom Spacing', 'blocksy'),
											'type' => 'ct-slider',
											'min' => 0,
											'max' => 100,
											'value' => 5,
											'responsive' => true,
											'sync' => [
												'id' => $prefix . 'related_order_skip'
											],
										],

									],
								],

								'post_meta' => [
									'label' => __('Post Meta', 'blocksy'),
									'clone' => true,
									'sync' => [
										'id' => $prefix . 'related_order_meta'
									],
									'options' => [
										blocksy_get_options('general/meta', [
											'is_cpt' => true,
											'computed_cpt' => trim($prefix, '_'),
											'skip_sync_id' => [
												'id' => $prefix . 'related_order_skip'
											],
											'meta_elements' => blocksy_post_meta_defaults([
												[
													'id' => 'post_date',
													'enabled' => true,
												],

												[
													'id' => 'comments',
													'enabled' => true,
												],
											])
										]),

										'spacing' => [
											'label' => __('Bottom Spacing', 'blocksy'),
											'type' => 'ct-slider',
											'min' => 0,
											'max' => 100,
											'value' => 20,
											'responsive' => true,
											'sync' => [
												'id' => $prefix . 'related_order_skip'
											],
										],
									]
								],
							],
						], trim($prefix, '_'), $prefix . 'related_order_skip'),
					],

					blocksy_rand_md5() => [
						'type' => 'ct-divider',
					],

					$prefix . 'related_posts_containment' => [
						'label' => __('Module Placement', 'blocksy'),
						'type' => 'ct-radio',
						'value' => 'separated',
						'view' => 'text',
						'design' => 'block',
						'desc' => __('Separate or unify the related posts module from or with the entry content area.', 'blocksy'),
						'choices' => [
							'separated' => __('Separated', 'blocksy'),
							'contained' => __('Contained', 'blocksy'),
						],
						'sync' => blocksy_sync_whole_page([
							'prefix' => $prefix,
						]),
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							'any' => [
								'all' => [
									$prefix . 'related_posts_containment' => 'separated',
									$prefix . 'comments_containment' => 'separated',
									$prefix . 'has_comments' => 'yes'
								],

								'all_' => [
									$prefix . 'related_posts_containment' => 'contained',
									$prefix . 'comments_containment' => 'contained',
									$prefix . 'has_comments' => 'yes'
								],
							]
						],
						'options' => [

							$prefix . 'related_location' => [
								'label' => __( 'Location', 'blocksy' ),
								'type' => 'ct-radio',
								'value' => 'before',
								'view' => 'text',
								'design' => 'block',
								'divider' => 'top',
								'choices' => [
									'before' => __( 'Before Comments', 'blocksy' ),
									'after' => __( 'After Comments', 'blocksy' ),
								],
								'sync' => blocksy_sync_whole_page([
									'prefix' => $prefix,
								]),
							],

						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							$prefix . 'related_posts_containment' => 'separated'
						],
						'options' => [

							$prefix . 'related_structure' => [
								'label' => __( 'Container Structure', 'blocksy' ),
								'type' => 'ct-radio',
								'value' => 'normal',
								'view' => 'text',
								'design' => 'block',
								'divider' => 'top',
								'choices' => [
									'normal' => __( 'Normal', 'blocksy' ),
									'narrow' => __( 'Narrow', 'blocksy' ),
								],
								'sync' => 'live'
							],

						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							$prefix . 'related_structure' => 'narrow',
							$prefix . 'related_posts_containment' => 'separated'
						],
						'options' => [
							$prefix . 'related_narrow_width' => [
								'label' => __( 'Container Max Width', 'blocksy' ),
								'type' => 'ct-slider',
								'value' => 750,
								'min' => 500,
								'max' => 800,
								'sync' => 'live'
							],
						],
					],

					$prefix . 'related_visibility' => [
						'label' => __( 'Visibility', 'blocksy' ),
						'type' => 'ct-visibility',
						'design' => 'block',
						'divider' => 'top',
						'sync' => 'live',
						'value' => blocksy_default_responsive_value([
							'desktop' => true,
							'tablet' => true,
							'mobile' => true,
						]),
						'choices' => blocksy_ordered_keys([
							'desktop' => __( 'Desktop', 'blocksy' ),
							'tablet' => __( 'Tablet', 'blocksy' ),
							'mobile' => __( 'Mobile', 'blocksy' ),
						]),
					],

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					$prefix . 'related_posts_label_font' => [
						'type' => 'ct-typography',
						'label' => __( 'Module Title Font', 'blocksy' ),
						'sync' => 'live',
						'value' => blocksy_typography_default_values([]),
					],

					$prefix . 'related_posts_label_color' => [
						'label' => __( 'Module Title Font Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'sync' => 'live',
						'value' => [
							'default' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],
						],
						'pickers' => [
							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default',
								'inherit' => [
									'var(--theme-heading-1-color, var(--theme-headings-color))' => [
										$prefix . 'related_label_wrapper' => 'h1'
									],

									'var(--theme-heading-2-color, var(--theme-headings-color))' => [
										$prefix . 'related_label_wrapper' => 'h2'
									],

									'var(--theme-heading-3-color, var(--theme-headings-color))' => [
										$prefix . 'related_label_wrapper' => 'h3'
									],

									'var(--theme-heading-4-color, var(--theme-headings-color))' => [
										$prefix . 'related_label_wrapper' => 'h4'
									],

									'var(--theme-heading-5-color, var(--theme-headings-color))' => [
										$prefix . 'related_label_wrapper' => 'h5'
									],

									'var(--theme-heading-6-color, var(--theme-headings-color))' => [
										$prefix . 'related_label_wrapper' => 'h6'
									],

									'var(--theme-text-color)' => [
										$prefix . 'related_label_wrapper' => 'span|p'
									],
								]
							],
						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							$prefix . 'related_order:array-ids:title:enabled' => '!no'
						],
						'options' => [

							$prefix . 'related_posts_link_font' => [
								'type' => 'ct-typography',
								'label' => __( 'Posts Title Font', 'blocksy' ),
								'sync' => 'live',
								'divider' => 'top:full',
								'value' => blocksy_typography_default_values([
									'size' => '16px'
								]),
							],

							$prefix . 'related_posts_link_color' => [
								'label' => __( 'Posts Title Font Color', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'inline',
								'sync' => 'live',
								'value' => [
									'default' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],

									'hover' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],
								],

								'pickers' => [
									[
										'title' => __( 'Initial', 'blocksy' ),
										'id' => 'default',
										'inherit' => [
											'var(--theme-heading-1-color, var(--theme-headings-color))' => [
												$prefix . 'related_order:array-ids:title:heading_tag' => 'h1'
											],

											'var(--theme-heading-2-color, var(--theme-headings-color))' => [
												$prefix . 'related_order:array-ids:title:heading_tag' => 'h2'
											],

											'var(--theme-heading-3-color, var(--theme-headings-color))' => [
												$prefix . 'related_order:array-ids:title:heading_tag' => 'h3'
											],

											'var(--theme-heading-4-color, var(--theme-headings-color))' => [
												$prefix . 'related_order:array-ids:title:heading_tag' => 'h4'
											],

											'var(--theme-heading-5-color, var(--theme-headings-color))' => [
												$prefix . 'related_order:array-ids:title:heading_tag' => 'h5'
											],

											'var(--theme-heading-6-color, var(--theme-headings-color))' => [
												$prefix . 'related_order:array-ids:title:heading_tag' => 'h6'
											],

											'var(--theme-text-color)' => [
												$prefix . 'related_order:array-ids:title:heading_tag' => 'span|p'
											],
										]
									],

									[
										'title' => __( 'Hover', 'blocksy' ),
										'id' => 'hover',
										'inherit' => 'var(--theme-link-hover-color)'
									],
								],
							],

						],
					],


					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							$prefix . 'related_order:array-ids:post_meta:enabled' => '!no'
						],
						'options' => [

							$prefix . 'related_posts_meta_font' => [
								'type' => 'ct-typography',
								'label' => __( 'Posts Meta Font', 'blocksy' ),
								'sync' => 'live',
								'divider' => 'top:full',
								'value' => blocksy_typography_default_values([
									'size' => '14px'
								]),
							],

							$prefix . 'related_posts_meta_color' => [
								'label' => __( 'Posts Meta Font Color', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'inline',
								'sync' => 'live',
								'value' => [
									'default' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],

									'hover' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],
								],

								'pickers' => [
									[
										'title' => __( 'Initial', 'blocksy' ),
										'id' => 'default',
										'inherit' => 'var(--theme-text-color)'
									],

									[
										'title' => __( 'Hover', 'blocksy' ),
										'id' => 'hover',
										'inherit' => 'var(--theme-link-hover-color)'
									],
								],
							],

						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-has-meta-category-button',
						'optionId' => $prefix . 'related_order',
						'options' => [

							$prefix . 'related_meta_button_type_font_colors' => [
								'label' => __( 'Meta Button Font Color', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'inline',
								'divider' => 'top',
								'noColor' => [ 'background' => 'var(--theme-text-color)'],
								'sync' => 'live',
								'value' => [
									'default' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],

									'hover' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],
								],

								'pickers' => [
									[
										'title' => __( 'Initial', 'blocksy' ),
										'id' => 'default',
										'inherit' => 'var(--theme-button-text-initial-color)'
									],

									[
										'title' => __( 'Hover', 'blocksy' ),
										'id' => 'hover',
										'inherit' => 'var(--theme-button-text-hover-color)'
									],
								],
							],

							$prefix . 'related_meta_button_type_background_colors' => [
								'label' => __( 'Meta Button Background', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'inline',
								'noColor' => [ 'background' => 'var(--theme-text-color)'],
								'sync' => 'live',
								'value' => [
									'default' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],

									'hover' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],
								],

								'pickers' => [
									[
										'title' => __( 'Initial', 'blocksy' ),
										'id' => 'default',
										'inherit' => 'var(--theme-button-background-initial-color)'
									],

									[
										'title' => __( 'Hover', 'blocksy' ),
										'id' => 'hover',
										'inherit' => 'var(--theme-button-background-hover-color)'
									],
								],
							],
						]
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							$prefix . 'related_order:array-ids:featured_image:enabled' => '!no'
						],
						'options' => [

							$prefix . 'related_thumb_radius' => [
								'label' => __( 'Image Border Radius', 'blocksy' ),
								'type' => 'ct-spacing',
								'divider' => 'top:full',
								'value' => blocksy_spacing_value(),
								'inputAttr' => [
									'placeholder' => '5'
								],
								'responsive' => true,
								'sync' => 'live',
							],

						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							$prefix . 'related_posts_containment' => 'separated'
						],
						'options' => [

							blocksy_rand_md5() => [
								'type' => 'ct-divider',
							],

							$prefix . 'related_posts_container_spacing' => [
								'label' => __( 'Container Inner Spacing', 'blocksy' ),
								'type' => 'ct-slider',
								'value' => '50px',
								'units' => blocksy_units_config([
									[ 'unit' => 'px', 'min' => 0, 'max' => 200],
									['unit' => '', 'type' => 'custom'],
								]),
								'responsive' => true,
								'sync' => 'live',
							],

							$prefix . 'related_posts_background' => [
								'label' => __( 'Container Background', 'blocksy' ),
								'type' => 'ct-background',
								'design' => 'inline',
								'divider' => 'top',
								'value' => blocksy_background_default_value([
									'backgroundColor' => [
										'default' => [
											'color' => 'var(--theme-palette-color-6)',
										],
									],
								]),
								'sync' => 'live',
							],

						],
					],
				],
			],
		],
	],
];

