<?php

namespace Blocksy\DbVersioning;

class V2067 {
	public function migrate() {
		$prefixes = blocksy_manager()->screen->get_archive_prefixes();

		foreach ($prefixes as $prefix) {
			$content_horizontal_alignment = get_theme_mod(
				$prefix . '_content_horizontal_alignment',
				'__empty__'
			);

			if ($content_horizontal_alignment === '__empty__') {
				continue;
			}

			if ($content_horizontal_alignment === 'left') {
				$content_horizontal_alignment = 'start';
			}

			if ($content_horizontal_alignment === 'right') {
				$content_horizontal_alignment = 'end';
			}

			set_theme_mod(
				$prefix . '_content_horizontal_alignment',
				$content_horizontal_alignment
			);
		}
	}
}

