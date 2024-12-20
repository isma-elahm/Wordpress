<?php
/**
 * Shares Widget
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package Blocksy
 */

$classes = blocksy_default_akg('className', $atts, '');
$color = blocksy_default_akg('share_icons_color', $atts, 'default');
$type = blocksy_default_akg('share_type', $atts, 'simple');
$fill = blocksy_default_akg('share_icons_fill', $atts, 'outline');
$icons_size = blocksy_akg('share_icons_size', $atts, '');
$items_spacing = blocksy_akg('items_spacing', $atts, '');

$colors = [];

$wrapper_attr = [
	'class' => trim('ct-shares-block ' . $classes),
	'style' => ''
];

if ($color !== 'official') {
	$colors['--theme-icon-color'] = blocksy_default_akg('customInitialColor', $atts, '');
	$colors['--theme-icon-hover-color'] = blocksy_default_akg('customHoverColor', $atts, '');

	if ($type !== 'simple') {
		$base_color = blocksy_default_akg('customBorderColor', $atts, 'rgba(218, 222, 228, 0.5)');
		$hover_color = blocksy_default_akg('customBorderHoverColor', $atts, 'rgba(218, 222, 228, 0.7)');

		if (isset($atts['borderColor'])) {
			$var = $atts['borderColor'];
			$base_color = "var(--wp--preset--color--$var)";
		}

		if (isset($atts['borderHoverColor'])) {
			$var = $atts['borderHoverColor'];
			$hover_color = "var(--wp--preset--color--$var)";
		}

		if ($fill === 'solid') {
			$base_color = blocksy_default_akg('customBackgroundColor', $atts, 'rgba(218, 222, 228, 0.5)');
			$hover_color = blocksy_default_akg('customBackgroundHoverColor', $atts, 'rgba(218, 222, 228, 0.7)');

			if (isset($atts['backgroundColor'])) {
				$var = $atts['backgroundColor'];
				$base_color = "var(--wp--preset--color--$var)";
			}

			if (isset($atts['backgroundHoverColor'])) {
				$var = $atts['backgroundHoverColor'];
				$hover_color = "var(--wp--preset--color--$var)";
			}
		}

		$colors = array_merge(
			$colors,
			[
				'--background-color' => $base_color,
				'--background-hover-color' => $hover_color
			]
		);
	}

	if (isset($atts['initialColor'])) {
		$var = $atts['initialColor'];
		$colors['--theme-icon-color'] = "var(--wp--preset--color--$var)";
	}

	if (isset($atts['hoverColor'])) {
		$var = $atts['hoverColor'];
		$colors['--theme-icon-hover-color'] = "var(--wp--preset--color--$var)";
	}

	foreach ($colors as $key => $value) {
		if (empty($value)) {
			continue;
		}

		$wrapper_attr['style'] .= $key . ':' . $value . ';';
	}
}

if (! empty($icons_size)) {
	$wrapper_attr['style'] .= '--theme-icon-size:' . $icons_size . 'px;';
}

if (! empty($items_spacing)) {
	$wrapper_attr['style'] .= '--items-spacing:' . $items_spacing . 'px;';
}

if (empty($wrapper_attr['style'])) {
	unset($wrapper_attr['style']);
}

echo '<div ' . blocksy_attr_to_html($wrapper_attr) . '>';

/**
 * blocksy_share_icons() function is already properly escaped.
 * Escaping it again here would cause SVG icons to not be outputed
 */

$attr = [];

$data_icons_type = $type;

if ($fill) {
	if ($type !== 'simple') {
		$data_icons_type .= ':' . $fill;
	}
}


// v.2.0.74 migration
$share_networks = blocksy_akg('share_networks', $atts, []);

if (empty($share_networks)) {
	foreach ($atts as $key => $value) {
		if ($value !== 'yes' && $value !== 'no') {
			continue;
		}
	
		if (strpos($key, 'share_') !== 0) {
			continue;
		}
	
		$share_networks[str_replace('share_', '', $key)] = [
			'id' => str_replace('share_', '', $key),
			'enabled' => $value === 'yes'
		];
	}
}

$atts['share_networks'] = $share_networks;
// v.2.0.74 migration

echo blocksy_get_social_share_box([
	'type' => 'share',
	'class' => '',
	'html_atts' => array_merge(
		[
			'data-type' => 'type-3',
		],
		$attr
	),
	'strategy' => [
		'strategy' => array_merge(
			$atts,
			[
				'share_box_visibility' => [
					'desktop' => true,
					'tablet' => true,
					'mobile' => true,
				],
				'share_box_title' => '',
			]
		),
	],
	'links_wrapper_attr' => [
		'data-icons-type' => $data_icons_type,
		'data-color' => $color
	],
]);

echo '</div>';
?>
