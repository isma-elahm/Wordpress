import { createElement } from '@wordpress/element'

const defaultEnabled = ['facebook', 'twitter', 'pinterest', 'linkedin']

const oldSocialNetworks = [
	'facebook',
	'twitter',
	'pinterest',
	'linkedin',
	'reddit',
	'hacker_news',
	'vk',
	'ok',
	'telegram',
	'viber',
	'whatsapp',
	'flipboard',
	'line',
	'email',
	'clipboard',
]

const oldAttributes = {
	title: {
		type: 'string',
		default: 'Share Icons',
	},
	share_facebook: {
		type: 'string',
		default: 'yes',
	},
	share_twitter: {
		type: 'string',
		default: 'yes',
	},
	share_pinterest: {
		type: 'string',
		default: 'yes',
	},
	share_linkedin: {
		type: 'string',
		default: 'yes',
	},
	share_reddit: {
		type: 'string',
		default: 'no',
	},
	share_hacker_news: {
		type: 'string',
		default: 'no',
	},
	share_vk: {
		type: 'string',
		default: 'no',
	},
	share_ok: {
		type: 'string',
		default: 'no',
	},
	share_telegram: {
		type: 'string',
		default: 'no',
	},
	share_viber: {
		type: 'string',
		default: 'no',
	},
	share_whatsapp: {
		type: 'string',
		default: 'no',
	},
	share_flipboard: {
		type: 'string',
		default: 'no',
	},
	share_line: {
		type: 'string',
		default: 'no',
	},
	share_email: {
		type: 'string',
		default: 'no',
	},
	share_clipboard: {
		type: 'string',
		default: 'no',
	},
	share_item_tooltip: {
		type: 'string',
		default: 'no',
	},
	link_nofollow: {
		type: 'string',
		default: 'no',
	},
	share_icons_size: {
		type: 'string',
		default: '',
	},
	items_spacing: {
		type: 'string',
		default: '',
	},
	share_icons_color: {
		type: 'string',
		default: 'default',
	},
	share_type: {
		type: 'string',
		default: 'simple',
	},
	share_icons_fill: {
		type: 'string',
		default: 'outline',
	},
	initialColor: {
		type: 'string',
		default: '',
	},
	customInitialColor: {
		type: 'string',
		default: '',
	},
	hoverColor: {
		type: 'string',
		default: '',
	},
	customHoverColor: {
		type: 'string',
		default: '',
	},
	borderColor: {
		type: 'string',
		default: 'rgba(218, 222, 228, 0.5)',
	},
	customBorderColor: {
		type: 'string',
		default: 'rgba(218, 222, 228, 0.5)',
	},
	borderHoverColor: {
		type: 'string',
		default: 'rgba(218, 222, 228, 0.7)',
	},
	customBorderHoverColor: {
		type: 'string',
		default: 'rgba(218, 222, 228, 0.7)',
	},
	backgroundColor: {
		type: 'string',
		default: 'rgba(218, 222, 228, 0.5)',
	},
	customBackgroundColor: {
		type: 'string',
		default: 'rgba(218, 222, 228, 0.5)',
	},
	backgroundHoverColor: {
		type: 'string',
		default: 'rgba(218, 222, 228, 0.7)',
	},
	customBackgroundHoverColor: {
		type: 'string',
		default: 'rgba(218, 222, 228, 0.7)',
	},
}

export default [
	{
		apiVersion: 3,

		isEligible: (attributes) => !attributes.share_networks,

		migrate: (prevAttributes) => {
			const share_networks = oldSocialNetworks.reduce((acc, key) => {
				if (prevAttributes[`share_${key}`]) {
					if (prevAttributes[`share_${key}`] === 'yes') {
						acc.push({
							id: key,
							enabled: true,
						})
					}
					return acc
				}

				if (defaultEnabled.includes(key)) {
					acc.push({
						id: key,
						enabled: true,
					})
				}

				return acc
			}, [])

			const cleanedAttributes = Object.keys(prevAttributes).reduce(
				(acc, key) => {
					if (oldSocialNetworks.includes(key.replace('share_', ''))) {
						return acc
					}

					acc[key] = prevAttributes[key]

					return acc
				},
				{}
			)

			return {
				...cleanedAttributes,

				// Create share_networks based on prevAttributes
				share_networks,
			}
		},

		attributes: oldAttributes,

		save: (props) => {
			return <div>Blocksy: Share Box</div>
		},
	},
]
