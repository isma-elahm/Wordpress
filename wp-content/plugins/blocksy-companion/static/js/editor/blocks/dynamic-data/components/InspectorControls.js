import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'

import { InspectorControls } from '@wordpress/block-editor'
import {
	RangeControl,
	PanelBody,
	TextareaControl,
	ExternalLink,
	TextControl,
} from '@wordpress/components'
import { OptionsPanel } from 'blocksy-options'

import { fieldIsImageLike } from '../utils'
import DimensionControls from './Dimensions'

const DynamicDataInspectorControls = ({
	fieldDescriptor,
	fieldsDescriptor,

	attributes,
	setAttributes,

	options,
	fieldsChoices,

	clientId,

	taxonomies,
}) => {
	return (
		<>
			<InspectorControls>
				<PanelBody>
					<OptionsPanel
						purpose="gutenberg"
						onChange={(optionId, optionValue) => {
							setAttributes({
								[optionId]: optionValue,
							})
						}}
						options={{
							field: {
								type: 'ct-select',
								label: __(
									'Content Source',
									'blocksy-companion'
								),
								value: '',
								search: true,
								searchPlaceholder: __(
									'Search for field',
									'blocksy-companion'
								),
								defaultToFirstItem: false,
								choices: fieldsChoices,
								purpose: 'default',
							},

							...(attributes.field === 'wp:terms' &&
							taxonomies &&
							taxonomies.length > 0
								? {
										taxonomy: {
											type: 'ct-select',
											label: __(
												'Taxonomy',
												'blocksy-companion'
											),
											value: '',
											design: 'inline',
											purpose: 'default',
											choices: taxonomies.map(
												({ name, slug }) => ({
													key: slug,
													value: name,
												})
											),
										},
								  }
								: {}),

							...(attributes.field === 'wp:term_image'
								? {
										imageSource: {
											type: 'ct-radio',
											label: __(
												'Image Source',
												'blocksy-companion'
											),
											value: attributes.imageSource,
											design: 'inline',
											purpose: 'gutenberg',
											divider: 'bottom',
											choices: {
												featured: __(
													'Image',
													'blocksy-companion'
												),
												icon: __(
													'Icon/Logo',
													'blocksy-companion'
												),
											},
										},
								  }
								: {}),

							...options,
						}}
						value={{
							...attributes,
							...(fieldsDescriptor &&
							fieldsDescriptor.has_taxonomies_customization
								? { has_taxonomies_customization: 'yes' }
								: {}),
						}}
						hasRevertButton={false}
					/>

					{fieldIsImageLike(fieldDescriptor) &&
						attributes.field !== 'wp:author_avatar' &&
						attributes.field !== 'wp:archive_image' && (
							<OptionsPanel
								purpose="gutenberg"
								onChange={(optionId, optionValue) => {
									setAttributes({
										[optionId]: optionValue,
									})
								}}
								options={{
									lightbox_condition: {
										type: 'ct-condition',
										condition: { has_field_link: 'no' },
										options: {
											lightbox: {
												type: 'ct-switch',
												label: __(
													'Expand on click',
													'blocksy-companion'
												),
												value: 'no',
											},
										},
									},

									...(attributes.field === 'wp:featured_image'
										? {
												videoThumbnail: {
													type: 'ct-switch',
													label: __(
														'Video thumbnail',
														'blocksy-companion'
													),
													value: 'no',
												},
										  }
										: {}),

									image_hover_effect: {
										label: __(
											'Image Hover Effect',
											'blocksy-companion'
										),
										type: 'ct-select',
										value: 'none',
										view: 'text',
										design: 'inline',
										choices: {
											none: __(
												'None',
												'blocksy-companion'
											),
											'zoom-in': __(
												'Zoom In',
												'blocksy-companion'
											),
											'zoom-out': __(
												'Zoom Out',
												'blocksy-companion'
											),
										},
									},
								}}
								value={attributes}
								hasRevertButton={false}
							/>
						)}
				</PanelBody>

				{fieldIsImageLike(fieldDescriptor) &&
					attributes.field !== 'wp:author_avatar' && (
						<>
							<DimensionControls
								clientId={clientId}
								attributes={attributes}
								setAttributes={setAttributes}
							/>

							<PanelBody>
								<TextareaControl
									label={__(
										'Alternative Text',
										'blocksy-companion'
									)}
									value={attributes.alt_text || ''}
									onChange={(value) => {
										setAttributes({
											alt_text: value,
										})
									}}
									help={
										<>
											<ExternalLink href="https://www.w3.org/WAI/tutorials/images/decision-tree">
												{__(
													'Describe the purpose of the image.',
													'blocksy-companion'
												)}
											</ExternalLink>
											<br />
											{__(
												'Leave empty if decorative.',
												'blocksy-companion'
											)}
										</>
									}
									__nextHasNoMarginBottom
								/>
							</PanelBody>
						</>
					)}

				{attributes.field === 'wp:author_avatar' && (
					<PanelBody>
						<RangeControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={__('Image size', 'blocksy-companion')}
							onChange={(newSize) =>
								setAttributes({
									avatar_size: newSize,
								})
							}
							min={5}
							max={500}
							initialPosition={attributes?.avatar_size}
							value={attributes?.avatar_size}
						/>
					</PanelBody>
				)}

				{attributes.field === 'woo:brands' && (
					<PanelBody>
						<RangeControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={__('Logo Size', 'blocksy-companion')}
							onChange={(newSize) =>
								setAttributes({
									brands_size: newSize,
								})
							}
							min={5}
							max={500}
							initialPosition={attributes?.brands_size}
							value={attributes?.brands_size}
						/>
						<RangeControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={__('Logo Gap', 'blocksy-companion')}
							onChange={(newGap) =>
								setAttributes({
									brands_gap: newGap,
								})
							}
							min={5}
							max={500}
							initialPosition={attributes?.brands_gap}
							value={attributes?.brands_gap}
						/>
					</PanelBody>
				)}

				{!fieldIsImageLike(fieldDescriptor) &&
					attributes.field !== 'woo:brands' && (
						<PanelBody>
							<OptionsPanel
								purpose="gutenberg"
								onChange={(optionId, optionValue) => {
									setAttributes({
										[optionId]: optionValue,
									})
								}}
								options={{
									before: {
										type: 'text',
										label: __(
											'Before',
											'blocksy-companion'
										),
										value: '',
									},

									after: {
										type: 'text',
										label: __('After', 'blocksy-companion'),
										value: '',
									},

									...(fieldDescriptor.provider !== 'wp' ||
									(fieldDescriptor.provider === 'wp' &&
										(fieldDescriptor.id === 'excerpt' ||
											fieldDescriptor.id === 'terms' ||
											fieldDescriptor.id === 'author'))
										? {
												fallback: {
													type: 'text',
													label: __(
														'Fallback',
														'blocksy-companion'
													),
													value: __(
														'Custom field fallback',
														'blocksy-companion'
													),
												},
										  }
										: {}),
								}}
								value={attributes}
								hasRevertButton={false}
							/>
						</PanelBody>
					)}
			</InspectorControls>

			{attributes.field === 'wp:terms' && (
				<InspectorControls group="advanced">
					<TextControl
						__nextHasNoMarginBottom
						autoComplete="off"
						label={__('Term additional class', 'blocksy-companion')}
						value={attributes.termClass}
						onChange={(nextValue) => {
							setAttributes({
								termClass: nextValue,
							})
						}}
						help={__(
							'Additional class for term items. Useful for styling.',
							'blocksy-companion'
						)}
					/>
				</InspectorControls>
			)}
		</>
	)
}

export default DynamicDataInspectorControls
