<?php

namespace Blocksy\Extensions\NewsletterSubscribe;

class MailchimpProvider extends Provider {
	public function __construct() {
	}

	public function fetch_lists($api_key, $api_url = '') {
		if (! $api_key) {
			return 'api_key_invalid';
		}

		if (strpos($api_key, '-') === false) {
			return 'api_key_invalid';
		}

		$region = explode('-', $api_key);

		if (strpos($region[1], '.') !== false) {
			return 'api_key_invalid';
		}

		$response = wp_remote_get(
			'https://' . $region[1] . '.api.mailchimp.com/3.0/lists?count=1000',
			[
				'timeout' => 2,
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode(
						'asd:' . $api_key
					)
				]
			]
		);

		if (! is_wp_error($response)) {
			if (200 !== wp_remote_retrieve_response_code($response)) {
				return 'api_key_invalid';
			}

			$body = json_decode(wp_remote_retrieve_body($response), true);

			if (! $body) {
				return 'api_key_invalid';
			}

			if (! isset($body['lists'])) {
				return 'api_key_invalid';
			}

			return array_map(function($list) {
				return [
					'name' => $list['name'],
					'id' => $list['id'],
					'subscribe_url_long' => $list['subscribe_url_long'],

					'subscribe_url_long_json' => str_replace(
						'subscribe',
						'subscribe/post-json',
						$list['subscribe_url_long'] . '&c=callback'
					),

					'has_gdpr_fields' => $list['marketing_permissions']
				];
			}, $body['lists']);
		} else {
			return 'api_key_invalid';
		}
	}

	public function get_form_url_and_gdpr_for($maybe_custom_list = null) {
		$settings = $this->get_settings();

		if (! isset($settings['api_key'])) {
			return false;
		}

		if (! $settings['api_key']) {
			return false;
		}

		$lists = $this->fetch_lists($settings['api_key']);

		if (! is_array($lists)) {
			return false;
		}

		if (empty($lists)) {
			return false;
		}

		if ($maybe_custom_list) {
			$settings['list_id'] = $maybe_custom_list;
		}

		if (! $settings['list_id']) {
			return [
				'form_url' => $lists[0]['subscribe_url_long'],
				'has_gdpr_fields' => $lists[0]['has_gdpr_fields'],
				'provider' => 'mailchimp'
			];
		}

		foreach ($lists as $single_list) {
			if ($single_list['id'] === $settings['list_id']) {
				return [
					'form_url' => $single_list['subscribe_url_long'],
					'has_gdpr_fields' => $single_list['has_gdpr_fields'],
					'provider' => 'mailchimp'
				];
			}
		}

		return [
			'form_url' => $lists[0]['subscribe_url_long'],
			'has_gdpr_fields' => $lists[0]['has_gdpr_fields'],
			'provider' => 'mailchimp'
		];
	}

	public function subscribe_form($args = []) {
		$args = wp_parse_args($args, [
			'email' => '',
			'name' => '',
		]);

		$settings = $this->get_settings();

		$curl = curl_init();

		$api_key = $settings['api_key'];

		$region = explode('-', $api_key);

		if (strpos($region[1], '.') !== false) {
			return 'api_key_invalid';
		}

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://' . $region[1] . '.api.mailchimp.com/3.0/lists/' . $settings['list_id'] . '/members/',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode([
				'email_address' => $args['email'],
				'first_name' => $args['name'],
				'status' => 'subscribed'
			]),
			CURLOPT_USERPWD => 'user:' . $settings['api_key'],
			CURLOPT_HTTPHEADER => array(
				"content-type: application/json; charset=utf-8"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return [
				'result' => 'no',
				'error' => $err
			];
		} else {
			$response = json_decode($response, true);
			
			if (
				isset($response['status'])
				&&
				$response['status'] === 400
			) {
				return [
					'result' => 'no',
					'message' => blocksy_safe_sprintf(
						__('%s is already a list member.', 'blocksy-companion'),
						$args['email']
					)
				];
			}

			return [
				'result' => 'yes',
				'message' => __('Thank you for subscribing to our newsletter!', 'blocksy-companion'),
				'res' => $response
			];
		}
	}
}

