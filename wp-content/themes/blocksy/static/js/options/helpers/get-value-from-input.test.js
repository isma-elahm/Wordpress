import { getValueFromInput } from './get-value-from-input'

test('it properly gets keys to mutate', () => {
	expect(
		getValueFromInput(
			{
				0: [
					{
						'5a35cdfd143f4b03888bc7f5aaec95e3': {
							type: 'ct-condition',
							condition: {
								show_on_front: 'posts',
							},
							values_source: 'global',
							options: {
								description: {
									label: false,
									type: 'textarea',
									value: '',
									disableRevertButton: true,
									design: 'block',
								},
							},
						},
					},
				],
				'78f71dc317a543ba78de430851dca893': {
					type: 'ct-condition',
					condition: {
						blog_hero_section: 'type-1',
					},
					values_source: 'parent',
					options: {
						hero_item_max_width: {
							label: 'Max Width',
							type: 'ct-slider',
							value: 100,
							min: 10,
							max: 100,
							defaultUnit: '%',
							responsive: true,
							sync: {
								id: 'blog_hero_elements_spacing',
							},
						},
					},
				},
				'25efeb6ffe1c0b6532b04d1c3a8c44dc': {
					type: 'ct-condition',
					condition: {
						itemIndex: '!0',
					},
					options: {
						hero_item_spacing: {
							label: 'Top Spacing',
							type: 'ct-slider',
							value: 20,
							min: 0,
							max: 100,
							responsive: true,
							sync: {
								id: 'blog_hero_elements_spacing',
							},
						},
					},
				},
				description_visibility: {
					label: 'Visibility',
					type: 'ct-visibility',
					design: 'block',
					value: {
						desktop: true,
						tablet: true,
						mobile: false,
					},
					choices: [
						{
							key: 'desktop',
							value: 'Desktop',
						},
						{
							key: 'tablet',
							value: 'Tablet',
						},
						{
							key: 'mobile',
							value: 'Mobile',
						},
					],
					sync: {
						id: 'blog_hero_elements_spacing',
					},
				},
			},
			{
				id: 'custom_description',
				enabled: true,
				description_visibility: {
					desktop: true,
					tablet: true,
					mobile: false,
				},
				id: 'custom_description',
				enabled: true,
				description_visibility: true,
				__id: 'AxjLaJMovFU9XViP7OBvo',
			}
		).description_visibility
	).toStrictEqual(true)
})
