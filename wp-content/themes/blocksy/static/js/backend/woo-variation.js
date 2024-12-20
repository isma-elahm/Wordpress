import { createElement, render } from '@wordpress/element'
import OptionsRoot from '../options/OptionsRoot'
import { getValueFromInput } from '../options/helpers/get-value-from-input'
import $ from 'jquery'
import { __ } from 'ct-i18n'

export const initWooVariation = (variationWrapper) => {
	let uploadImage = variationWrapper.querySelector('.upload_image')

	if (!uploadImage) {
		return
	}

	if (uploadImage.closest('.form-flex-box')) {
		uploadImage = uploadImage.closest('.form-flex-box')
	} else {
		uploadImage = uploadImage.nextElementSibling
	}

	const div = document.createElement('div')

	div.classList.add('form-row')
	div.classList.add('form-row-full')
	div.classList.add('ct-variation-image-gallery')

	uploadImage.insertAdjacentElement('afterend', div)

	const maybeWpmlLocked = document.querySelector('.wcml_lock_img')

	const input = variationWrapper.querySelector(
		'[name*="blocksy_post_meta_options"]'
	)

	if (!input) {
		return
	}

	const options = {
		gallery_source: {
			label: __('Variation Gallery', 'blocksy'),
			type: maybeWpmlLocked ? 'hidden' : 'ct-radio',
			value: 'default',
			design: 'inline',
			divider: 'bottom',
			choices: {
				default: __('Default', 'blocksy'),
				custom: __('Custom', 'blocksy'),
			},
		},

		...(maybeWpmlLocked
			? {
					inheritance: {
						label: __('Variation Gallery', 'blocksy'),
						type: 'ct-notification',
						design: 'inline',
						text: maybeWpmlLocked.outerHTML
							.replace('wcml_lock_img', '')
							.replace('display: none;', ''),
					},
			  }
			: {}),

		condition: maybeWpmlLocked
			? {
					type: 'hidden',
			  }
			: {
					type: 'ct-condition',
					condition: {
						gallery_source: 'custom',
					},
					options: {
						images: {
							label: __('Custom Image Gallery', 'blocksy'),
							type: 'ct-multi-image-uploader',
							design: ({ value }) =>
								value.length === 0 ? 'inline' : 'block',
							value: [],
						},
					},
			  },
	}

	render(
		<OptionsRoot
			options={options}
			value={getValueFromInput(
				options,
				JSON.parse(input.value),
				null,
				false
			)}
			input_id={input.id}
			input_name={input.name}
			hasRevertButton={false}
		/>,
		div
	)
}

export const initAllWooVariations = () => {
	;[
		...document.querySelectorAll(
			'.woocommerce_variations .woocommerce_variation'
		),
	].map((variationWrapper) => {
		if (variationWrapper.hasBlocksyOptions) {
			return
		}

		variationWrapper.hasBlocksyOptions = true

		initWooVariation(variationWrapper)
	})
}
