<?php

$options = [
	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:excerpt'],
		'options' => [
			'excerpt_length' => [
				'label' => __('Length', 'blocksy-companion'),
				'type' => 'ct-number',
				'design' => 'inline',
				'value' => 40,
				'min' => 1,
				'max' => 300,
			],
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:date'],
		'options' => [
			'date_type' => [
				'label' => __('Date type', 'blocksy-companion'),
				'type' => 'ct-select',
				'value' => 'published',
				'design' => 'inline',
				'purpose' => 'default',
				'choices' => blocksy_ordered_keys(
					[
						'published' => __('Published Date', 'blocksy-companion'),
						'modified' => __('Modified Date', 'blocksy-companion'),
					]
				),
			],

			'default_format' => [
				'type'  => 'ct-switch',
				'label' => __('Default format', 'blocksy-companion'),
				'value' => 'yes',
				'desc' => __('Example: January 24, 2022', 'blocksy-companion'),
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => ['default_format' => 'no'],
				'options' => [
					'date_format' => [
						'label' => __('Date type', 'blocksy-companion'),
						'type' => 'ct-select',
						'value' => 'F j, Y',
						'design' => 'inline',
						'purpose' => 'default',
						'choices' => blocksy_ordered_keys(
							[
								'F j, Y' => date_i18n('F j, Y'),
								'Y-m-d' => date_i18n('Y-m-d'),
								'm/d/Y' => date_i18n('m/d/Y'),
								'd/m/Y' => date_i18n('d/m/Y'),
								'd.m.Y' => date_i18n('d.m.Y'),
								'd-m-Y' => date_i18n('d-m-Y'),
								'd.m.Y.' => date_i18n('d.m.Y.'),
								'd-m-Y' => date_i18n('d-m-Y'),
								'custom' => __('Custom', 'blocksy-companion'),
							]
						),
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => ['date_format' => 'custom'],
						'options' => [
							'custom_date_format' => [
								'type' => 'text',
								'label' => __('Custom date format', 'blocksy-companion'),
								'value' => 'F j, Y',
								'desc' => blocksy_safe_sprintf(
									'%s <a href="%s" target="_blank">format string</a>',
									__('Enter a date or time', 'blocksy-companion'),
									'https://wordpress.org/documentation/article/customize-date-and-time-format/'
								),
							],
						]
					]
				]
			],
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:comments'],
		'options' => [
			'zero_text' => [
				'type' => 'text',
				'label' => __('No comments', 'blocksy-companion'),
				'value' => __('No comments', 'blocksy-companion'),
			],

			'single_text' => [
				'type' => 'text',
				'label' => __('One comment', 'blocksy-companion'),
				'value' => __('One comment', 'blocksy-companion'),
			],

			'multiple_text' => [
				'type' => 'text',
				'label' => __('Multiple comments', 'blocksy-companion'),
				'value' => __('% comments', 'blocksy-companion'),
			]
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:terms'],
		'options' => [
			'separator' => [
				'type' => 'text',
				'label' => __('Separator', 'blocksy-companion'),
				'value' => ', ',
			],
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:author'],
		'options' => [
			'author_field' => [
				'type' => 'ct-select',
				'label' => __('Author Field', 'blocksy-companion'),
				'value' => 'display_name',
				'design' => 'inline',
				'purpose' => 'default',
				'choices' => blocksy_ordered_keys(
					[
						'display_name' => __('Display Name', 'blocksy-companion'),
						'nicename' => __('Nickname', 'blocksy-companion'),
						'first_name' => __('First Name', 'blocksy-companion'),
						'last_name' => __('Last Name', 'blocksy-companion'),
						'description' => __('Description', 'blocksy-companion'),
						'email' => __('Email', 'blocksy-companion'),
					]
				),
			]
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [
			'field' => implode('|', [
				'wp:title',
				'wp:date',
				'wp:author',
				'wp:terms',
				'wp:comments',

				'wp:term_title',
				'wp:term_image',
				'wp:term_count',

				'wp:author_avatar',
				'wp:featured_image',
			])
		],
		'options' => [
			'has_field_link' => [
				'type'  => 'ct-switch',
				'label' => [
					__('Link to post', 'blocksy-companion') => [
						'field' => 'wp:title|wp:date|wp:comments'
					],

					__('Link to author page', 'blocksy-companion') => [
						'field' => 'wp:author|wp:author_avatar'
					],

					__('Link to term page', 'blocksy-companion') => [
						'field' => 'wp:terms'
					],

					__('Link to archive page', 'blocksy-companion') => [
						'field' => 'wp:term_title|wp:term_image|wp:term_count'
					],
				],
				'value' => 'no',
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => ['has_field_link' => 'yes'],
				'options' => [
					'has_field_link_new_tab' => [
						'type'  => 'ct-switch',
						'label' => __('Open in new tab', 'blocksy-companion'),
						'value' => 'no',
					],

					'has_field_link_rel' => [
						'type' => 'text',
						'label' => __('Link Rel', 'blocksy-companion'),
						'value' => '',
					],
				]
			]
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [
			'field' => 'wp:terms',
			'has_taxonomies_customization' => 'yes'
		],
		'options' => [
			'termAccentColor' => [
				'type'  => 'ct-switch',
				'label' => __('Terms accent color', 'blocksy-companion'),
				'divider' => 'top:full',
				'value' => 'yes',
			]
		]
	]
];

