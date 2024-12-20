import {
	createElement,
	Component,
	createRef,
	useState,
	useEffect,
	useRef,
} from '@wordpress/element'
import { __, sprintf } from 'ct-i18n'
import $ from 'jquery'
import cls from 'classnames'

import { wpUpdatesAjax } from '../helpers/wp-updates'

const VersionMismatchNotice = ({
	className,
	mismatched_version_descriptor = {},
}) => {
	mismatched_version_descriptor = {
		productName: 'Blocksy theme',
		slug: 'blocksy',

		...mismatched_version_descriptor,
	}

	const [isLoading, setIsLoading] = useState(false)

	return (
		<div className={cls('ct-theme-required', className)}>
			<h2>
				<span>
					<svg viewBox="0 0 24 24">
						<path d="M12,23.6c-1.4,0-2.6-1-2.8-2.3L8.9,20h6.2l-0.3,1.3C14.6,22.6,13.4,23.6,12,23.6z M24,17.8H0l3.1-2c0.5-0.3,0.9-0.7,1.1-1.3c0.5-1,0.5-2.2,0.5-3.2V7.6c0-4.1,3.2-7.3,7.3-7.3s7.3,3.2,7.3,7.3v3.6c0,1.1,0.1,2.3,0.5,3.2c0.3,0.5,0.6,1,1.1,1.3L24,17.8zM6.1,15.6h11.8c0,0-0.1-0.1-0.1-0.2c-0.7-1.3-0.7-2.9-0.7-4.2V7.6c0-2.8-2.2-5.1-5.1-5.1c-2.8,0-5.1,2.2-5.1,5.1v3.6c0,1.3-0.1,2.9-0.7,4.2C6.1,15.5,6.1,15.6,6.1,15.6z" />
					</svg>
				</span>
				{sprintf(
					__(
						'Action required - please update %s to the latest version!',
						'blocksy-companion'
					),
					mismatched_version_descriptor.productName
				)}
			</h2>

			<p
				dangerouslySetInnerHTML={{
					__html: sprintf(
						__(
							'We detected that you are using an outdated version of %s.',
							'blocksy-companion'
						),
						mismatched_version_descriptor.productName
					),
				}}
			/>

			<p
				dangerouslySetInnerHTML={{
					__html: __(
						'In order to take full advantage of all features the core has to offer - please install and activate the latest version of Blocksy theme.',
						'blocksy-companion'
					),

					__html: sprintf(
						__(
							'In order to take full advantage of all features the core has to offer - please install and activate the latest version of %s.',
							'blocksy-companion'
						),
						mismatched_version_descriptor.productName
					),
				}}
			/>

			<button
				className="button button-primary"
				onClick={(e) => {
					e.preventDefault()

					setIsLoading(true)

					const data = {
						success: (...a) => {
							setTimeout(() => {
								location.reload()
							}, 1000)
						},
						error: (...a) => {
							setTimeout(() => {
								location.reload()
							}, 1000)
						},
					}

					if (mismatched_version_descriptor.slug === 'blocksy') {
						data.slug = 'blocksy'
					} else {
						data.plugin = mismatched_version_descriptor.slug
						data.slug =
							mismatched_version_descriptor.slug.split('/')[0]
					}

					wpUpdatesAjax(
						mismatched_version_descriptor.slug === 'blocksy'
							? 'update-theme'
							: 'update-plugin',
						data
					)
				}}>
				{isLoading
					? __('Loading...', 'blocksy-companion')
					: sprintf(
							__('Update %s Now', 'blocksy-companion'),
							mismatched_version_descriptor.productName
								.split(' ')
								.map(
									(word) =>
										word.charAt(0).toUpperCase() +
										word.slice(1)
								)
								.join(' ')
					  )}
			</button>
		</div>
	)
}

export default VersionMismatchNotice
