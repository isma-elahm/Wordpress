<?php

function blocksy_safe_antispambot($string_with_email) {
	$mail_parts = wp_parse_url($string_with_email);

	$mail_parts['path'] = antispambot($mail_parts['path']);

	$result = [];

	if (! empty($mail_parts['scheme'])) {
		$result[] = $mail_parts['scheme'] . ':';
	}

	$result[] = $mail_parts['path'];

	if (! empty($mail_parts['query'])) {
		$result[] = '?' . $mail_parts['query'];
	}

	return implode('', $result);
}

/**
 * Generate attributes string for html tag
 *
 * @param array $attr_array array('href' => '/', 'title' => 'Test').
 *
 * @return string 'href="/" title="Test"'
 */
if (! function_exists('blocksy_attr_to_html')) {
	function blocksy_attr_to_html(array $attr_array) {
		$html_attr = '';

		foreach ($attr_array as $attr_name => $attr_val) {
			if (false === $attr_val) {
				continue;
			}

			$html_attr .= $attr_name . '="' . esc_attr($attr_val) . '" ';
		}

		return trim($html_attr);
	}
}

/**
 * Generate html tag
 *
 * @param string      $tag Tag name.
 * @param array       $attr Tag attributes.
 * @param bool|string $end Append closing tag. Also accepts body content.
 *
 * @return string The tag's html
 */
if (! function_exists('blocksy_html_tag')) {
	function blocksy_html_tag($tag, $attr = [], $end = false) {
		if (! is_string($attr)) {
			$attr = blocksy_attr_to_html($attr);
		}

		if (strpos($tag, ' ') !== false) {
			$tag = explode(' ', $tag)[0];
		}

		$html = '<' . $tag;

		if (! empty($attr)) {
			$html .= ' ' . $attr;
		}

		if (true === $end) {
			// <script></script>
			$html .= '></' . $tag . '>';
		} elseif (false === $end) {
			// <br>
			$html .= '>';
		} else {
			// <div>content</div>
			$html .= '>' . $end . '</' . $tag . '>';
		}

		return $html;
	}
}
