<?php

namespace Blocksy\DbVersioning;

class V2074 {
	public function migrate() {
		$forms_font_size = get_theme_mod(
			'formFontSize',
			'__empty__'
		);

		if ($forms_font_size === '__empty__') {
			return;
		}

		set_theme_mod(
			'form_font',
			blocksy_typography_default_values(
				[
					'size' => $forms_font_size
				]
			)
		);
	}
}
