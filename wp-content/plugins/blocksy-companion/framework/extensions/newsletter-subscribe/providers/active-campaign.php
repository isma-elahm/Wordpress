<?php

namespace Blocksy\Extensions\NewsletterSubscribe;

class ActiveCampaignProvider extends Provider {
	public function fetch_lists($api_key, $api_url) {

		if (! $api_url) {
			return 'api_url_invalid';
		}
		
		if (! $api_key) {
			return 'api_key_invalid';
		}

		$response = wp_remote_get(
			"{$api_url}/api/3/lists",
			[
				'headers' => [
					'Api-Token' => $api_key,
					'accept' => 'application/json',
				]
			]
		);		
		
		if (! is_wp_error($response)) {
			if (200 !== wp_remote_retrieve_response_code($response)) {
				return 'api_key_invalid';
			}

			$body = json_decode(wp_remote_retrieve_body($response), true);

			if (! $body || ! isset($body['lists'])) {
				return 'api_key_invalid';
			}

			return array_map(function($list) {
				return [
					'name' => $list['name'],
					'id' => $list['id'],
				];
			}, $body['lists']);
		} else {
			return 'api_key_invalid';
		}
	}

	public function get_form_url_and_gdpr_for($maybe_custom_list = null) {
		return [
			'form_url' => '#',
			'has_gdpr_fields' => false,
			'provider' => 'activecampaign'
		];
	}

	public function subscribe_form($args = []) {
		$args = wp_parse_args($args, [
			'email' => '',
			'name' => '',
		]);

		$settings = $this->get_settings();

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "{$settings['api_url']}/api/3/contacts",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode([
				'contact' => [
					"email" => $args['email'],
          			"firstName" => $args['name'],
				]
			]),
			CURLOPT_HTTPHEADER => array(
				"Api-Token: " . $settings['api_key'],
				"accept: application/json",
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

			if (isset($response['errors'][0])) {
				return [
					'result' => 'no',
					'message' => $response['errors'][0]['title']
				];
			}

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => "{$settings['api_url']}/api/3/contactLists",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => json_encode([
					"contactList" => [
						"list" => $settings['list_id'],
						"contact" => $response['contact']['id'],
						"status" => 1
					]
				]),
				CURLOPT_HTTPHEADER => array(
					"Api-Token: " . $settings['api_key'],
					"accept: application/json",
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
	
				if (isset($response['errors'][0])) {
					return [
						'result' => 'no',
						'message' => $response['errors'][0]['title']
					];
				}
			}

			return [
				'result' => 'yes',
				'message' => __('Thank you for subscribing to our newsletter!', 'blocksy-companion')
			];
		}
	}
}

