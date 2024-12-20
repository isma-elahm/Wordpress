<?php

namespace Blocksy\Extensions\NewsletterSubscribe;

class FluentCRMProvider extends Provider {
    public function __construct() {
	}

	public function fetch_lists($api_key, $api_url = '') {
		
        if (! class_exists(\FluentCrm\App\Models\Lists::class)) {
            return 'api_key_invalid';
        }

		$lists = \FluentCrm\App\Models\Lists::orderBy('title', 'ASC')->get();
		
        $formattedLists = [];
        foreach ($lists as $list) {
            $formattedLists[] = [
                'name' => $list->title,
                'id' => strval($list->id)
            ];
        }

		return $formattedLists;
	}

	public function get_form_url_and_gdpr_for($maybe_custom_list = null) {
		return [
			'form_url' => '#',
			'has_gdpr_fields' => false,
			'provider' => 'fluentcrm',
            'list_id' => $maybe_custom_list
		];
	}

	public function subscribe_form($args = []) {
        $args = wp_parse_args($args, [
			'email' => '',
			'name' => '',
            'group' => ''
		]);

        if (! class_exists(\FluentCrm\App\Services\Funnel\FunnelHelper::class)) {
            return [
                'result' => 'no',
                'error' => 'FluentCrm API not found'
            ];
        }

        $settings = $this->get_settings();

        $subscriber = [
            'email' => $args['email'],
            'full_name' => $args['name']
        ];

        try {
            $get_subscriber = \FluentCrm\App\Services\Funnel\FunnelHelper::getSubscriber($args['email']);
        } catch (\Exception $e) {
            return [
                'result' => 'no',
                'message' => $e->getMessage()
            ];
        }

        if ($get_subscriber) {
            return [
                'result' => 'no',
                'message' => 'Subscriber already exists'
            ];
        }

        $list_id = intval($settings['list_id']);

        if (! empty($args['group'])) {
            $list_id = intval($args['group']);
        }

        $subscriber['lists'] = [$list_id];

        try {
            \FluentCrm\App\Services\Funnel\FunnelHelper::createOrUpdateContact($subscriber);
        } catch (\Exception $e) {
            return [
                'result' => 'no',
                'message' => $e->getMessage()
            ];
        }

        return [
            'list' => $args,
            'result' => 'yes',
            'message' => __('Thank you for subscribing to our newsletter!', 'blocksy-companion')
        ];
	}
}

