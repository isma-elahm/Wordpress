<?php
/**
 * Contact Info widget
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */
$is_pro = function_exists('blc_fs') && blc_fs()->can_use_premium_code();

$visibility_option = [
	'visibility' => [
		'label' => __( 'Element Visibility', 'blocksy-companion' ),
		'type' => 'ct-visibility',
		'design' => 'block',
		// 'allow_empty' => true,
		'value' => blocksy_default_responsive_value([
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		]),

		'choices' => blocksy_ordered_keys([
			'desktop' => __( 'Desktop', 'blocksy-companion' ),
			'tablet' => __( 'Tablet', 'blocksy-companion' ),
			'mobile' => __( 'Mobile', 'blocksy-companion' ),
		]),
	],
];

$options = [
	'title' => [
		'type' => 'hidden',
		'label' => __('Title', 'blocksy-companion'),
		'value' => __('Contact Info', 'blocksy-companion'),
	],

	'contact_text' => [
		'label' => __('Text', 'blocksy-companion'),
		'type' => 'hidden',
	],

	'contact_information' => [
		'label' => __('Contact Information', 'blocksy-companion'),
		'type' => 'ct-layers',
		'manageable' => true,
		'value' => [
			[
				'id' => 'address',
				'enabled' => true,
				'title' => __('Address:', 'blocksy-companion'),
				'content' => 'Street Name, NY 38954',
				'link' => '',
			],

			[
				'id' => 'phone',
				'enabled' => true,
				'title' => __('Phone:', 'blocksy-companion'),
				'content' => '578-393-4937',
				'link' => 'tel:578-393-4937',
			],

			[
				'id' => 'mobile',
				'enabled' => true,
				'title' => __('Mobile:', 'blocksy-companion'),
				'content' => '578-393-4937',
				'link' => 'tel:578-393-4937',
			],
		],

		'settings' => [
			'address' => [
				'label' => __('Address', 'blocksy-companion'),
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy-companion'),
							'value' => __('Address:', 'blocksy-companion'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy-companion'),
							'value' => 'Street Name, NY 38954',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy-companion'),
							'value' => '',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy-companion'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy-companion'),
									'custom' => __('Custom', 'blocksy-companion'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy-companion'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-map-pin',
										],
									],
								],
							],
						]
						: [],

					$visibility_option
				],

				'clone' => true,
			],

			'phone' => [
				'label' => __('Phone', 'blocksy-companion'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy-companion'),
							'value' => __('Phone:', 'blocksy-companion'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy-companion'),
							'value' => '578-393-4937',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy-companion'),
							'value' => 'tel:578-393-4937',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy-companion'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy-companion'),
									'custom' => __('Custom', 'blocksy-companion'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy-companion'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-phone',
										],
									],
								],
							],
						]
						: [],

					$visibility_option
				],
			],

			'mobile' => [
				'label' => __('Mobile', 'blocksy-companion'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy-companion'),
							'value' => __('Mobile:', 'blocksy-companion'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy-companion'),
							'value' => '578-393-4937',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy-companion'),
							'value' => 'tel:578-393-4937',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy-companion'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy-companion'),
									'custom' => __('Custom', 'blocksy-companion'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy-companion'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-mobile-phone',
										],
									],
								],
							],
						]
						: [],

					$visibility_option
				],
			],

			'hours' => [
				'label' => __('Work Hours', 'blocksy-companion'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy-companion'),
							'value' => __('Opening hours', 'blocksy-companion'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy-companion'),
							'value' => '9AM - 5PM',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy-companion'),
							'value' => '',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy-companion'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy-companion'),
									'custom' => __('Custom', 'blocksy-companion'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy-companion'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-clock',
										],
									],
								],
							],
						]
						: [],

					$visibility_option
				],
			],

			'fax' => [
				'label' => __('Fax', 'blocksy-companion'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy-companion'),
							'value' => __('Fax:', 'blocksy-companion'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy-companion'),
							'value' => '578-393-4937',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy-companion'),
							'value' => 'tel:578-393-4937',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy-companion'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy-companion'),
									'custom' => __('Custom', 'blocksy-companion'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy-companion'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-fax',
										],
									],
								],
							],
						]
						: [],

					$visibility_option
				],
			],

			'email' => [
				'label' => __('Email', 'blocksy-companion'),
				'clone' => 2,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy-companion'),
							'value' => __('Email:', 'blocksy-companion'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy-companion'),
							'value' => 'contact@yourwebsite.com',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy-companion'),
							'value' => 'mailto:contact@yourwebsite.com',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy-companion'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy-companion'),
									'custom' => __('Custom', 'blocksy-companion'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy-companion'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-email',
										],
									],
								],
							],
						]
						: [],

					$visibility_option
				],
			],

			'website' => [
				'label' => __('Website', 'blocksy-companion'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy-companion'),
							'value' => __('Website:', 'blocksy-companion'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy-companion'),
							'value' => 'creativethemes.com',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy-companion'),
							'value' => 'https://creativethemes.com',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy-companion'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy-companion'),
									'custom' => __('Custom', 'blocksy-companion'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy-companion'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-globe',
										],
									],
								],
							],
						]
						: [],

					$visibility_option
				],
			],
		],
	],

	'contact_link_target' => [
		'type' => 'ct-switch',
		'label' => __('Open link in new tab', 'blocksy-companion'),
		'value' => 'no',
		'divider' => 'top:full'
	],

	'link_nofollow' => [
		'type'  => 'ct-switch',
		'label' => __( 'Set links to nofollow', 'blocksy-companion' ),
		'value' => 'no',
	],

	'link_icons' => [
		'type'  => 'ct-switch',
		'label' => __( 'Link Icons', 'blocksy-companion' ),
		'value' => 'no',
	],

	'contacts_icons_size' => [
		'label' => __( 'Icons Size', 'blocksy-companion' ),
		'type' => 'ct-slider',
		'min' => 5,
		'max' => 50,
		'value' => 20,
		'responsive' => false,
		'divider' => 'top:full',
	],

	'contacts_items_spacing' => [
		'label' => __( 'Items Spacing', 'blocksy-companion' ),
		'type' => 'ct-slider',
		'min' => 5,
		'max' => 50,
		'value' => '',
		'responsive' => false,
	],

	'contacts_icon_shape' => [
		'label' => __('Icons Shape Type', 'blocksy-companion'),
		'type' => 'ct-radio',
		'value' => 'rounded',
		'view' => 'text',
		'divider' => 'top:full',
		'setting' => ['transport' => 'postMessage'],
		'choices' => [
			'simple' => __('None', 'blocksy-companion'),
			'rounded' => __('Rounded', 'blocksy-companion'),
			'square' => __('Square', 'blocksy-companion'),
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['contacts_icon_shape' => '!simple'],
		'options' => [
			'contacts_icon_fill_type' => [
				'label' => __('Shape Fill Type', 'blocksy-companion'),
				'type' => 'ct-radio',
				'value' => 'outline',
				'view' => 'text',
				'setting' => ['transport' => 'postMessage'],
				'choices' => [
					'outline' => __('Outline', 'blocksy-companion'),
					'solid' => __('Solid', 'blocksy-companion'),
				],
			],
		],
	],

	'contacts_items_direction' => [
		'type' => 'ct-radio',
		'label' => __( 'Items Direction', 'blocksy-companion' ),
		'view' => 'text',
		'design' => 'block',
		'divider' => 'top:full',
		'value' => 'column',
		'choices' => [
			'column' => __( 'Vertical', 'blocksy-companion' ),
			'row' => __( 'Horizontal', 'blocksy-companion' ),
		],
		'setting' => [ 'transport' => 'postMessage' ],
	],
];
