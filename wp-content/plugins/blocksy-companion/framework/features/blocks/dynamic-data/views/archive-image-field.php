<?php

if (! isset($term_id)) {
	$term_id = null;
}

$aspect_ratio = blocksy_akg('aspectRatio', $attributes, 'auto');
$image_source = blocksy_akg('imageSource', $attributes, 'featured');
$image_fit = blocksy_akg('imageFit', $attributes, 'cover');
$height = blocksy_akg('height', $attributes, '');

$lightbox = blocksy_akg('lightbox', $attributes, '');
$image_hover_effect = blocksy_akg('image_hover_effect', $attributes, '');

$has_field_link = blocksy_akg('has_field_link', $attributes, 'no');
$alt_text = blocksy_akg('alt_text', $attributes, '');

$img_atts = [
	'style' => ''
];

$wrapper_attr = [
	'class' => 'ct-dynamic-media'
];

$link_attr = [];

$classes = [];
$styles = [];

$border_result = get_block_core_post_featured_image_border_attributes(
	$attributes
);

// Aspect aspectRatio with a height set needs to override the default width/height.
if (! empty($aspect_ratio)) {
	$img_atts['style'] .= 'width:100%;height:100%;';
} elseif (! empty($height) ) {
	$img_atts['style'] .= "height:{$attributes['height']};";
}

$img_atts['style'] .= "object-fit: {$image_fit};";

if (! empty($alt_text)) {
	$img_atts['alt'] = $alt_text;
}

if (
	! empty($attributes['aspectRatio'])
	&&
	$aspect_ratio !== 'auto'
) {
	$img_atts['style'] .= 'aspect-ratio: ' . $aspect_ratio . ';';
}

if ($image_hover_effect === 'none') {
	if (! empty($border_result['class'])) {
		$img_atts['class'] = $border_result['class'];
	}

	if (! empty($border_result['style'])) {
		$img_atts['style'] .= $border_result['style'];
	}
}

$attachment_id = null;

if (! $term_id && is_archive()) {
	$term_id = get_queried_object_id();
}

if (! $term_id && function_exists('is_shop') && is_shop()) {
	$post_id = get_option('woocommerce_shop_page_id');
	$attachment_id = get_post_thumbnail_id($post_id);
}

if (! $term_id && is_home() && ! is_front_page()) {
	$post_id = get_option('page_for_posts');
	$attachment_id = get_post_thumbnail_id($post_id);
}

if ($term_id) {
	$id = get_term_meta($term_id, 'thumbnail_id');

	if ($id && !empty($id)) {
		$attachment_id = $id[0];
	}

	if (! $id) {
		$attachment_id = null;
	}

	$term_atts = get_term_meta(
		$term_id,
		'blocksy_taxonomy_meta_options'
	);

	if (empty($term_atts)) {
		$term_atts = [[]];
	}

	$maybe_image = blocksy_akg('image', $term_atts[0], '');

	if ($image_source === 'icon') {
		$maybe_image = blocksy_akg('icon_image', $term_atts[0], '');
	}

	if (
		$maybe_image
		&&
		is_array($maybe_image)
		&&
		isset($maybe_image['attachment_id'])
	) {
		$attachment_id = $maybe_image['attachment_id'];
	}

	$has_field_link = blocksy_akg('has_field_link', $attributes, 'no');

	if ($has_field_link === 'yes') {
		$link_attr = [
			'href' => get_term_link($term_id)
		];

		$has_field_link_new_tab = blocksy_akg('has_field_link_new_tab', $attributes, 'no');
		$has_field_link_rel = blocksy_akg('has_field_link_rel', $attributes, '');

		if ($has_field_link_new_tab !== 'no') {
			$link_attr['target'] = '_blank';
		}

		if (! empty($has_field_link_rel)) {
			$link_attr['rel'] = $has_field_link_rel;
		}
	}
}

if (empty($attachment_id)) {
	return;
}

// $value = blocksy_media([
// 	'attachment_id' => $attachment_id,
// 	'size' => blocksy_akg('sizeSlug', $attributes, 'full'),
// 	'ratio' => $attributes['aspectRatio'],
// 	'fit' => $image_fit,
// 	'img_atts' => $img_atts
// ]);

$value = wp_get_attachment_image(
	$attachment_id,
	blocksy_akg('sizeSlug', $attributes, 'full'),
	false,
	$img_atts
);

if (empty($value)) {
	return;
}

if (! empty($attributes['width'])) {
	$styles[] = 'width: ' . $attributes['width'] . ';';
}

if (! empty($attributes['height'])) {
	$styles[] = 'height: ' . $attributes['height'] . ';';
}

if (! empty($attributes['imageAlign'])) {
	$classes[] = 'align' . $attributes['imageAlign'];
}

if (! empty($attributes['className'])) {
	$classes[] = $attributes['className'];
}

$wrapper_attr['class'] .= ' ' . implode(' ', $classes);
$wrapper_attr['style'] = implode(' ', $styles);

if ($image_hover_effect !== 'none') {
	$span_styles = [];
	$span_classes = ['ct-dynamic-media-inner'];

	if (! empty($border_result['style'])) {
		$span_styles[] = $border_result['style'];
	}

	if (! empty($border_result['class'])) {
		$span_classes[] = $border_result['class'];
	}

	$value = blocksy_html_tag(
		'span',
		[
			'data-hover' => $image_hover_effect,
			'class' => implode(' ', $span_classes),
			'style' => implode(' ', $span_styles)
		],
		$value
	);
}

$tag_name = 'figure';

if (! empty($link_attr)) {
	$tag_name = 'a';
	$wrapper_attr = array_merge(
		$wrapper_attr,
		$link_attr
	);
}

$wrapper_attr = get_block_wrapper_attributes($wrapper_attr);

if (
	$lightbox === 'yes'
	&&
	function_exists('block_core_image_render_lightbox')
	&&
	$has_field_link !== 'yes'
) {
	echo block_core_image_render_lightbox(blocksy_html_tag($tag_name, $wrapper_attr, $value), []);

	return;
}

echo blocksy_html_tag($tag_name, $wrapper_attr, $value);
