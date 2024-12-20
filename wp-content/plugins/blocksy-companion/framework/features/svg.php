<?php

namespace Blocksy;

class SvgHandling {
	public function __construct() {
		add_filter(
			'wp_handle_upload_prefilter',
			function ($file) {
				$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

				if ('svg' !== $extension) {
					return $file;
				}

				$svg_content = file_get_contents($file['tmp_name']);

				// Sanitize the SVG content
				$sanitized_content = $this->cleanup_svg($svg_content);

				file_put_contents($file['tmp_name'], $sanitized_content);

				return $file;
			}
		);

		add_filter(
			'wp_check_filetype_and_ext',
			function ($data = null, $file = null, $filename = null, $mimes = null) {
				if (strpos($filename, '.svg') !== false) {
					$data['type'] = 'image/svg+xml';
					$data['ext'] = 'svg';
				}

				return $data;
			},
			75, 4
		);

		add_filter('upload_mimes', function ($mimes) {
			$mimes['svg'] = 'image/svg+xml';
			return $mimes;
		});

		add_filter(
			'wp_get_attachment_metadata',
			[$this, 'filter_get_attachment_metadata'],
			10, 2
		);

		add_filter(
			'wp_update_attachment_metadata',
			[$this, 'filter_get_attachment_metadata'],
			10, 2
		);

		add_filter(
			'wp_get_attachment_image_src',
			function ($image, $attachment_id, $size, $icon) {
				if (! isset($attachment_id)) {
					return $image;
				}

				$mime = get_post_mime_type($attachment_id);

				if (
					'image/svg+xml' === $mime
					&&
					$image[1] === 1
					&&
					$image[2] === 1
				) {
					$dimensions = $this->get_dimensions_for($attachment_id);

					$image[2] = $dimensions['height'];
					$image[1] = $dimensions['width'];
				}

				return $image;
			},
			10, 4
		);
	}

	public function filter_get_attachment_metadata($data, $attachment_id) {
		$mime = get_post_mime_type($attachment_id);

		if (
			'image/svg+xml' === $mime
			&&
			(
				! isset($data['width'])
				||
				! isset($data['height'])
			)
		) {
			$dimensions = $this->get_dimensions_for($attachment_id);

			$data['width'] = $dimensions['width'];
			$data['height'] = $dimensions['height'];
		}

		return $data;
	}

	public function get_dimensions_for($attachment_id) {
		$height = 100;
		$width = 100;

		$maybe_file = get_attached_file($attachment_id);

		if ($maybe_file) {
			$dimensions = $this->svg_dimensions($maybe_file);

			if ($dimensions) {
				$height = round($dimensions['height']);
				$width = round($dimensions['width']);
			}
		}

		return [
			'height' => $height,
			'width' => $width
		];
	}

	public function svg_dimensions($svg) {
		if (
			! preg_match('/.svg$/', $svg)
			||
			! file_exists($svg)
		) {
			return null;
		}

		$svg = file_get_contents($svg);

		$attributes = new \stdClass();

		if ($svg && function_exists('simplexml_load_string')) {
			$svg = @simplexml_load_string($svg);

			if ($svg) {
				foreach ($svg->attributes() as $key => $value) {
					$attributes->{$key} = strval($value);
				}
			}
		}

		if (
			! isset($attributes->width)
			&&
			$svg
			&&
			function_exists('xml_parser_create')
		) {
			$xml = xml_parser_create('UTF-8');

			$svgData = new \stdClass();

			xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
			xml_set_element_handler(
				$xml,
				function ($parser, $name, $attrs) use (&$svgData) {
					if ($name === 'SVG') {
						if (isset($attrs['WIDTH'])) {
							$attrs['width'] = $attrs['WIDTH'];
						}

						if (isset($attrs['HEIGHT'])) {
							$attrs['height'] = $attrs['HEIGHT'];
						}

						if (isset($attrs['VIEWBOX'])) {
							$attrs['viewBox'] = $attrs['VIEWBOX'];
						}

						foreach ($attrs as $key => $value) {
							$svgData->{$key} = $value;
						}
					}
				},
				function ($parser, $tag) {
				}
			);

			if (xml_parse($xml, $svg, true)) {
				$attributes = $svgData;
			}

			xml_parser_free($xml);
		}

		$width = 0;
		$height = 0;

		if (empty($attributes)) {
			return false;
		}

		if (
			isset($attributes->width, $attributes->height)
			&&
			is_numeric($attributes->width)
			&&
			is_numeric($attributes->height)
		) {
			$width = floatval($attributes->width);
			$height = floatval($attributes->height);
		} elseif (isset($attributes->viewBox)) {
			$sizes = explode(' ', $attributes->viewBox);

			if (isset($sizes[2], $sizes[3])) {
				$width = floatval($sizes[2]);
				$height = floatval($sizes[3]);
			}
		} else {
			return false;
		}

		return [
			'width' => $width,
			'height' => $height,
			'orientation' => ($width > $height) ? 'landscape' : 'portrait'
		];
	}

	public function cleanup_svg($content) {
		$base_path = BLOCKSY_PATH . 'vendor/svg-sanitizer/src';

		require_once($base_path . '/data/AttributeInterface.php');
		require_once($base_path . '/data/TagInterface.php');
		require_once($base_path . '/data/AllowedAttributes.php');
		require_once($base_path . '/data/AllowedTags.php');
		require_once($base_path . '/data/XPath.php');
		require_once($base_path . '/ElementReference/Resolver.php');
		require_once($base_path . '/ElementReference/Subject.php');
		require_once($base_path . '/ElementReference/Usage.php');
		require_once($base_path . '/Exceptions/NestingException.php');
		require_once($base_path . '/Helper.php');
		require_once($base_path . '/Sanitizer.php');

		$sanitizer = new \blocksy\enshrined\svgSanitize\Sanitizer();

		return $sanitizer->sanitize($content);
	}
}

