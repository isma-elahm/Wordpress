<?php

namespace Blocksy;

class Capabilities {
	private $accounts_cache = null;
	private $plans = [];
	private $plan = '__DEFAULT__';

	private $module_slug = 'blocksy-companion';

	private $wp_capabilities = [
		'dashboard' => 'manage_options',
		'custom_post_type' => 'manage_options',
		'conditions' => 'manage_options',

		'ext_code_snippets_fields' => 'edit_posts'
	];

	public function __construct() {
		// Drop current cache if it is broken.
		$option_cache = wp_cache_get('fs_accounts', 'options');

		if (
			$option_cache
			&&
			strpos(
				json_encode($option_cache),
				'__PHP_Incomplete_Class'
			) !== false
		) {
			wp_cache_delete('fs_accounts', 'options');
		}

		$this->accounts_cache = blc_get_option_from_db('fs_accounts');

		$for_plans = $this->accounts_cache;

		if (is_multisite()) {
			// Drop current cache if it is broken.
			$network_id = get_current_network_id();

			$cache_key = "$network_id:fs_accounts";

			$network_option_cache = wp_cache_get($cache_key, 'site-options');

			if (
				$network_option_cache
				&&
				strpos(
					json_encode($network_option_cache),
					'__PHP_Incomplete_Class'
				) !== false
			) {
				wp_cache_delete($cache_key, 'site-options');
			}

			$for_plans = blc_get_network_option_from_db(null, 'fs_accounts');
		}

		if (
			isset($for_plans['plans'])
			&&
			isset($for_plans['plans'][$this->module_slug])
		) {
			$this->plans = $for_plans['plans'][$this->module_slug];
		}
	}

	public function get_features() {
		// possible plans:
		// free
		//
		// personal
		// professional
		// agency
		//
		// personal_v2
		// professional_v2
		// agency_v2
		return [
			'base_pro' => [
				'personal',
				'professional',
				'agency',

				'personal_v2',
				'professional_v2',
				'agency_v2'
			],

			'pro_starter_sites' => [
				'personal',
				'professional',
				'agency',

				'personal_v2',
				'professional_v2',
				'agency_v2'
			],

			'pro_starter_sites_enhanced' => [
				'personal',
				'professional',
				'agency',

				'professional_v2',
				'agency_v2'
			],

			'post_types_extra' => [
				'personal',
				'professional',
				'agency',

				'professional_v2',
				'agency_v2'
			],

			'shop_extra' => [
				'personal',
				'professional',
				'agency',

				'professional_v2',
				'agency_v2'
			],

			'white_label' => [
				'agency',
				'agency_v2'
			]
		];
	}

	public function has_feature($feature = 'base_pro') {
		$plan = $this->get_plan();

		$features = $this->get_features();

		if (! isset($features[$feature])) {
			return false;
		}

		return in_array($plan, $features[$feature]);
	}

	public function get_plan() {
		if ($this->plan !== '__DEFAULT__') {
			return $this->plan;
		}

		if (! blc_can_use_premium_code()) {
			$this->plan = 'free';
			return 'free';
		}

		$site = $this->get_site();

		if (
			! $site
			||
			! isset($site->plan_id)
			||
			empty($this->plans)
		) {
			return 'free';
		}

		$plan_id = null;

		foreach ($this->plans as $incomplete_plan) {
			$plan = $this->casttoclass('stdClass', $incomplete_plan);

			if (! isset($plan->id) || empty($plan->id)) {
				continue;
			}

			$id = base64_decode($plan->id);

			if (strval($id) === strval($site->plan_id)) {
				$plan_id = strval($id);
			}
		}

		$plans = [
			'8504' => 'free',

			'11880' => 'personal',
			'11881' => 'professional',
			'11882' => 'agency',

			'23839' => 'personal_v2',
			'23840' => 'professional_v2',
			'23841' => 'agency_v2'
		];

		if ($plan_id) {
			if (isset($plans[$plan_id])) {
				$this->plan = $plans[$plan_id];
				return $plans[$plan_id];
			} else {
				$this->plan = 'agency_v2';
				return 'agency_v2';
			}
		}

		$this->plan = 'free';
		return 'free';
	}

	public function get_wp_capability_by($scope, $scope_details = []) {
		if (! isset($this->wp_capabilities[$scope])) {
			return false;
		}

		return apply_filters(
			'blocksy:capabilities:wp_capability',
			$this->wp_capabilities[$scope],
			$scope,
			$scope_details
		);
	}

	private function get_site() {
		$site = null;

		if (
			! isset($this->accounts_cache)
			||
			! isset($this->accounts_cache['sites'])
			||
			! isset($this->accounts_cache['sites'][$this->module_slug])
		) {
			return null;
		}

		$maybe_site = $this->casttoclass(
			'stdClass',
			$this->accounts_cache['sites'][$this->module_slug]
		);

		return $maybe_site;
	}

	private function casttoclass($class, $object) {
		return unserialize(
			preg_replace(
				'/^O:\d+:"[^"]++"/',
				'O:' . strlen($class) . ':"' . $class . '"',
				serialize($object)
			)
		);
	}
}

