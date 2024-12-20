import {
	createElement,
	Component,
	useEffect,
	useState,
	createContext,
	useContext,
	Fragment,
} from '@wordpress/element'

import classnames from 'classnames'
import { __ } from 'ct-i18n'

import { getNameForPlugin } from './Wizzard/Plugins'

import { DemosContext } from '../DemoInstall'
import useProExtensionInFree from '../../helpers/useProExtensionInFree'

const smartJoin = (arr, type = 'conjunction') => {
	const formatter = new Intl.ListFormat('en-GB', {
		style: 'long',
		type,
	})

	return formatter.format(arr)
}

const SingleDemo = ({ demo }) => {
	const {
		currentlyInstalledDemo,
		demos_list,
		setCurrentDemo,
		setInstallerBlockingReleased,
	} = useContext(DemosContext)

	const { isProInFree, showNotice, content } = useProExtensionInFree(
		{
			config: {
				pro: !!demo.is_pro,

				...(demo.plans
					? {
							plans: demo.plans,
					  }
					: {}),
			},
		},

		{
			strategy: 'pro',

			personal: {
				title: __('This is a Pro starter site', 'blocksy-companion'),
				description: __(
					'Upgrade to any pro plan and get instant access to this starter site and many other features.',
					'blocksy-companion'
				),
			},

			professional: {
				description: __(
					'Upgrade to the business or agency plan and get instant access to this starter site and many other features.',
					'blocksy-companion'
				),
			},

			agency: {
				description: __(
					'Upgrade to the agency plan and get instant access to this starter site and many other features.',
					'blocksy-companion'
				),
			},
		}
	)

	return (
		<Fragment>
			<li
				className={classnames('ct-single-demo', {
					'ct-is-pro': demo.is_pro,
				})}>
				<figure>
					<img src={demo.screenshot} />

					<section>
						{demo.is_pro && (
							<Fragment>
								<h3>
									{__('Required plan', 'blocksy-companion')}
								</h3>

								<span className="ct-required-plans">
									{smartJoin(
										(demo.plans
											? demo.plans
											: [
													'personal_v2',
													'professional_v2',
													'agency_v2',
											  ]
										)
											.filter((plan) => {
												return plan.indexOf('v2') > -1
											})
											.map((plan) => {
												return {
													personal_v2: __(
														'Personal',
														'blocksy-companion'
													),
													professional_v2: __(
														'Business',
														'blocksy-companion'
													),
													agency_v2: __(
														'Agency',
														'blocksy-companion'
													),
												}[plan]
											}),
										'disjunction'
									)}
								</span>
							</Fragment>
						)}

						<h3>{__('Available for', 'blocksy-companion')}</h3>
						<span className="ct-available-builders">
							{smartJoin(
								demos_list
									.filter(
										({ name }) => name === demo.name || ''
									)

									.sort((a, b) => {
										if (a.builder < b.builder) {
											return -1
										}

										if (a.builder > b.builder) {
											return 1
										}

										return 0
									})
									.map(
										({ builder }) =>
											getNameForPlugin(builder) ||
											'Gutenberg'
									)
							)}
						</span>
					</section>

					{demo.is_pro && <span className="ct-pro-badge">PRO</span>}
				</figure>

				<div className="ct-demo-actions">
					<h4>{demo.name}</h4>

					<div>
						<a
							className="ct-button"
							target="_blank"
							href={demo.url}>
							{__('Preview', 'blocksy-companion')}
						</a>
						<button
							className="ct-button-primary"
							onClick={() => {
								if (isProInFree) {
									showNotice()
									return
								} else {
									setInstallerBlockingReleased(false)
									setCurrentDemo(demo.name)
								}
							}}>
							{currentlyInstalledDemo &&
							currentlyInstalledDemo.demo.indexOf(demo.name) > -1
								? __('Modify', 'blocksy-companion')
								: __('Import', 'blocksy-companion')}
						</button>
					</div>
				</div>
			</li>

			{content}
		</Fragment>
	)
}

export default SingleDemo
