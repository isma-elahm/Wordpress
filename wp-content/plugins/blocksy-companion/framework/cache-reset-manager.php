<?php

namespace Blocksy;

class CacheResetManager {
	public function is_there_any_page_caching() {
		$page_is_cached = false;

		if (class_exists('W3TC\Cache')) {
			$w3_conf = \W3TC\Dispatcher::config();
			$page_is_cached = !! $w3_conf->get_boolean('pgcache.enabled');
		}

		if (class_exists('LiteSpeed\Control')) {
			$control = \LiteSpeed\Root::cls('Control');

			$page_is_cached = (
				$control->conf('cache') && \LiteSpeed\Control::is_cacheable()
			);
		}

		if (class_exists('WPO_Cache_Config')) {
			$cache_config = \WPO_Cache_Config::instance();
			$page_is_cached = !! $cache_config->get_option('enable_page_caching');
		}

		if (class_exists('SiteGround_Optimizer\Options\Options')) {
			$siteground_optimizer_file_caching = \SiteGround_Optimizer\Options\Options::is_enabled(
				'siteground_optimizer_file_caching'
			);

			$siteground_optimizer_enable_cache = \SiteGround_Optimizer\Options\Options::is_enabled(
				'siteground_optimizer_enable_cache'
			);

			$page_is_cached = (
				$siteground_optimizer_file_caching
				||
				$siteground_optimizer_enable_cache
			);
		}

		if (class_exists('\WP_Rocket\Buffer\Cache')) {
			$page_is_cached = \WP_Rocket\Buffer\Cache::can_generate_caching_files();
		}

		if (class_exists("\CF\API\Plugin")) {
			if (\CF\API\Plugin::SETTING_PLUGIN_SPECIFIC_CACHE === "plugin_specific_cache") {
				$page_is_cached = true;
			}
		}

		global $super_cache_enabled;

		if (isset($super_cache_enabled)) {
			$page_is_cached = $super_cache_enabled;
		}

		return apply_filters(
			'blocksy:cache-manager:page-is-cached',
			$page_is_cached
		);
	}
}

