import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'

import {
	BaseControl,
	Flex,
	FlexItem,
	__experimentalNumberControl as NumberControl,
	RangeControl as NativeRangeControl,
} from '@wordpress/components'

const RangeControl = ({ label, onChange, value }) => {
	return (
		<BaseControl label={label}>
			<Flex gap={4}>
				<FlexItem isBlock>
					<NumberControl
						size="__unstable-large"
						onChange={(value) => onChange(+value)}
						value={value}
						min={1}
						label={label}
						hideLabelFromVision
					/>
				</FlexItem>
				<FlexItem isBlock>
					<NativeRangeControl
						value={parseInt(value, 10)} // RangeControl can't deal with strings.
						onChange={(value) => onChange(+value)}
						min={1}
						max={6}
						withInputField={false}
						label={label}
						hideLabelFromVision
					/>
				</FlexItem>
			</Flex>
		</BaseControl>
	)
}

export default RangeControl
