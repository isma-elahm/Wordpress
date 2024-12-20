import { __ } from 'ct-i18n'

export const computeRequestsForDemoContent = ({
	currentDemoWithVariation,
	demoContent: { content },
}) => {
	return [
		{
			title: __('Import content', 'blocksy-companion'),
			params: {
				action: 'blocksy_demo_install_content',
				demo_name: currentDemoWithVariation,
			},
		},
	]
}
