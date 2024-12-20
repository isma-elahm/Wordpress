<?php

namespace Blocksy;

class DemoInstallPluginsUninstaller {
	public function import() {
		if (! current_user_can('edit_theme_options')) {
			wp_send_json_error([
				'message' => __("Sorry, you don't have permission to uninstall plugins.", 'blocksy-companion')
			]);
		}

		if (! isset($_REQUEST['plugins']) || !$_REQUEST['plugins']) {
			wp_send_json_success();
		}

		$plugins = explode(':', $_REQUEST['plugins']);

		$plugins_manager = Plugin::instance()->demo->get_plugins_manager();

		foreach ($plugins as $single_plugin) {
			$plugins_manager->plugin_deactivation($single_plugin);
		}

		wp_send_json_success();
	}
}


