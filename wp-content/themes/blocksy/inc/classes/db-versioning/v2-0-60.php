<?php

namespace Blocksy\DbVersioning;

class V2060 {
	public function migrate() {
		$value = get_option(
			'blocksy_ext_local_google_fonts_settings',
			'__empty__'
		);

		if ($value === '__empty__' || ! is_array($value)) {
			return;
		}

		update_option(
			'blocksy_ext_local_google_fonts_settings',
			$value,
			false
		);
	}
}


