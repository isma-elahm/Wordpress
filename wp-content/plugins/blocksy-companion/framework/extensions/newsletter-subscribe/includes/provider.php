<?php

namespace Blocksy\Extensions\NewsletterSubscribe;

class Provider {
	static public function get_for_settings() {
		$m = new Provider();
		$settings = $m->get_settings();

		return Provider::get_for_provider(
			$settings['provider']
		);
	}

	static public function get_for_provider($provider) {
		if ($provider === 'mailchimp') {
			return new MailchimpProvider();
		}

		if ($provider === 'brevo') {
			return new BrevoProvider();
		}

		if ($provider === 'campaignmonitor') {
			return new CampaignMonitorProvider();
		}

		if ($provider === 'mailerlite-new') {
			return new MailerliteNewProvider();
		}

		if ($provider === 'convertkit') {
			return new ConvertKitProvider();
		}

		if ($provider === 'demo') {
			return new DemoProvider();
		}

		if ($provider === 'mailpoet') {
			return new MailPoetProvider();
		}

		if ($provider === 'activecampaign') {
			return new ActiveCampaignProvider();
		}

		if ($provider === 'fluentcrm') {
			return new FluentCRMProvider();
		}

		return new MailerliteClassicProvider();
	}

	public function fetch_lists($api_key, $api_url) {
		return [];
	}

	public function get_settings() {
		$option = get_option('blocksy_ext_mailchimp_credentials', []);

		if (empty($option)) {
			$option = [];
		}

		$free_providers = ['mailchimp', 'demo'];

		if (
			isset($option['provider'])
			&&
			! in_array($option['provider'], $free_providers)
			&&
			blc_get_capabilities()->get_plan() === 'free'
		) {
			$option['provider'] = $free_providers[0];
		}

		return array_merge([
			'provider' => 'mailchimp',
			'api_key' => null,
			'list_id' => null
		], $option);
	}

	public function set_settings($vals) {
		update_option('blocksy_ext_mailchimp_credentials', array_merge([
			'provider' => 'mailchimp',
			'api_key' => null,
			'list_id' => null
		], $vals));
	}

	public function can($capability = 'manage_options') {
		if (is_multisite()) {
			// Only network admin can change files that affects the entire network.
			$can = current_user_can_for_blog( get_current_blog_id(), $capability );
		} else {
			$can = current_user_can( $capability );
		}

		if ($can) {
			// Also you can use this method to get the capability.
			$can = $capability;
		}

		return $can;
	}
}

