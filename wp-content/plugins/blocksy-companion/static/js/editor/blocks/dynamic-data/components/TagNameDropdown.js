import { createElement } from '@wordpress/element'
import { ToolbarDropdownMenu } from '@wordpress/components'
import { __, sprintf } from 'ct-i18n'

import TagNameIcon from './TagNameIcon'

export default function HeadingLevelDropdown({ tagName, onChange }) {
	return (
		<ToolbarDropdownMenu
			popoverProps={{
				className: 'block-library-heading-level-dropdown',
			}}
			icon={<TagNameIcon level={tagName} />}
			label={__('Change heading level', 'blocksy-companion')}
			controls={[
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
				'p',
				'span',
				'div',
			].map((targetTagName) => {
				{
					const isActive = targetTagName === tagName

					return {
						icon: (
							<TagNameIcon
								level={targetTagName}
								isPressed={isActive}
							/>
						),
						label: targetTagName,
						title: {
							h1: __('Heading 1', 'blocksy-companion'),
							h2: __('Heading 2', 'blocksy-companion'),
							h3: __('Heading 3', 'blocksy-companion'),
							h4: __('Heading 4', 'blocksy-companion'),
							h5: __('Heading 5', 'blocksy-companion'),
							h6: __('Heading 6', 'blocksy-companion'),
							p: __('Paragraph', 'blocksy-companion'),
							span: __('Span', 'blocksy-companion'),
							div: __('Div', 'blocksy-companion'),
						}[targetTagName],
						isActive,
						onClick() {
							onChange(targetTagName)
						},
						role: 'menuitemradio',
					}
				}
			})}
		/>
	)
}
