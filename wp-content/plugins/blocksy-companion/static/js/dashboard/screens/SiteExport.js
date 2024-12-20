import {
	createElement,
	Component,
	useEffect,
	useState,
	Fragment,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import classnames from 'classnames'
import useActivationAction from '../helpers/useActivationAction'
import fileSaver from 'file-saver'
import Overlay from '../../helpers/Overlay'

import { getPluginsMap } from './DemoInstall/Wizzard/Plugins'

const SiteExport = ({ allPlans, allCategories }) => {
	const [isLoading, setIsLoading] = useState(false)
	const [isShowing, setIsShowing] = useState(false)

	const [remoteDemos, setRemoteDemos] = useState([])

	const [demoId, setDemoId] = useState(null)

	const [builder, setBuilder] = useState('')
	const [plugins, setPlugins] = useState([])

	useEffect(() => {
		const fetchInitialData = async () => {
			const body = new FormData()

			body.append('action', 'blocksy_demo_get_export_data')
			body.append(
				'nonce',
				ctDashboardLocalizations.dashboard_actions_nonce
			)

			try {
				const response = await fetch(
					ctDashboardLocalizations.ajax_url,
					{
						method: 'POST',
						body,
					}
				)

				if (response.status === 200) {
					const { success, data } = await response.json()

					if (success) {
						const fields = data.data

						if (fields && fields.builder) {
							setBuilder(fields.builder)
						}

						if (fields && fields.plugins) {
							setPlugins(fields.plugins)
						}

						if (fields && fields.demoId) {
							setDemoId(parseFloat(fields.demoId))
						}
					}
				}
			} catch (e) {}
		}

		const fetchRemoteDemos = async () => {
			try {
				const response = await fetch(
					'https://creativethemes.com/blocksy/wp-json/ct/v1/starter-sites',
					{
						method: 'GET',
					}
				)

				if (response.status === 200) {
					const data = await response.json()
					setRemoteDemos(data)
				}
			} catch (e) {}
		}

		fetchInitialData()
		fetchRemoteDemos()
	}, [])

	const downloadExport = async () => {
		setIsLoading(true)

		const body = new FormData()

		body.append('action', 'blocksy_demo_export')
		body.append('nonce', ctDashboardLocalizations.dashboard_actions_nonce)

		body.append('demoId', demoId)
		body.append('builder', builder)
		body.append('plugins', plugins.join(','))

		body.append('wp_customize', 'on')

		try {
			const response = await fetch(ctDashboardLocalizations.ajax_url, {
				method: 'POST',
				body,
			})

			if (response.status === 200) {
				const { success, data } = await response.json()

				if (success) {
					const remoteDemo = remoteDemos.find(
						(demo) => demo.id === demoId
					)

					const finalDemo = {
						name: remoteDemo.title,
						url: remoteDemo.demo_live_link,
						is_pro: remoteDemo.is_pro === 'Pro',

						categories: remoteDemo.categories,
						keywords: remoteDemo.keywords,

						...(remoteDemo.is_pro === 'Pro' && remoteDemo.plans
							? {
									plans: [
										'personal',
										'professional',
										'agency',

										...remoteDemo.plans.map((plan) => {
											return `${plan}_v2`
										}),
									],
							  }
							: {}),

						...data.demo,
					}

					if (finalDemo.builder.toLowerCase() === 'elementor') {
						finalDemo.elementor_experiment_container = true
					}

					console.log('Blocksy:Dashboard:DemoInstall:exported', {
						remoteDemo,
						finalDemo,
					})

					var blob = new Blob([JSON.stringify(finalDemo)], {
						type: 'text/plain;charset=utf-8',
					})

					fileSaver.saveAs(blob, `${remoteDemo.title}.json`)
				}
			}
		} catch (e) {}

		setIsLoading(false)
	}

	return (
		<div className="ct-filter-trigger-export">
			<button
				type="button"
				className="components-button has-icon has-text"
				onClick={(e) => {
					setIsShowing(true)
				}}>
				<svg
					aria-hidden="true"
					width="24"
					height="24"
					viewBox="0 0 24 24"
					fillRule="evenodd"
					fill="currentColor">
					<path d="M20 15v5H4v-5h1.5v3.5h6V6.8l-4 4-1-1.1L12.2 4l6.3 5.7-1 1.1L13 6.7v11.8h5.5V15H20z" />
				</svg>

				{__('Export Site')}
			</button>

			<Overlay
				items={isShowing}
				className="ct-site-export-modal"
				onDismiss={() => setIsShowing(false)}
				render={() => (
					<div className="ct-modal-content">
						<h2>Export Settings</h2>

						<div className="ct-site-export-settings ct-modal-scroll">
							<section className="general-section has-divider">
								<label>
									<span className="ct-label">
										{__(
											'Starter site',
											'blocksy-companion'
										)}
									</span>

									<select
										value={demoId}
										onChange={({ target: { value } }) =>
											setDemoId(parseFloat(value))
										}>
										<option value="">
											{__(
												'Select a starter site',
												'blocksy-companion'
											)}
										</option>
										{remoteDemos
											.sort((a, b) => {
												const fa = a.title.toLowerCase()
												const fb = b.title.toLowerCase()

												if (fa < fb) {
													return -1
												}
												if (fa > fb) {
													return 1
												}

												return 0
											})
											.map((demo) => (
												<option
													value={demo.id}
													key={demo.id}>
													{demo.title}
												</option>
											))}
									</select>
								</label>

								<label>
									<span className="ct-label">
										{__('Builder', 'blocksy-companion')}
									</span>

									<input
										type="text"
										placeholder={__(
											'Builder',
											'blocksy-companion'
										)}
										value={builder}
										onChange={({ target: { value } }) =>
											setBuilder(value)
										}
									/>
								</label>
							</section>

							<section className="plugins-section">
								<h4>Required plugins</h4>

								<div className="ct-bundled-plugins-list grid-labels">
									{Object.keys(getPluginsMap())
										.filter(
											(plugin) => plugin !== 'gutenberg'
										)
										.map((plugin) => (
											<label
												tabindex="0"
												onClick={(e) => {
													e.preventDefault()

													setPlugins((plugins) => {
														if (
															plugins.includes(
																plugin
															)
														) {
															return plugins.filter(
																(p) =>
																	p !== plugin
															)
														}

														return [
															...plugins,
															plugin,
														]
													})
												}}>
												<input
													type="checkbox"
													checked={
														plugins.indexOf(
															plugin
														) > -1
													}
													onChange={({
														target: { checked },
													}) => {}}
												/>

												<span>
													{getPluginsMap()[plugin]}
												</span>
											</label>
										))}
								</div>
							</section>
						</div>

						<div className="ct-modal-actions has-divider">
							<button
								className="button button-primary"
								disabled={isLoading}
								onClick={() => downloadExport()}>
								{isLoading
									? __('Loading...', 'blocksy-companion')
									: __('Export site', 'blocksy-companion')}
							</button>
						</div>
					</div>
				)}
			/>
		</div>
	)
}

export default SiteExport
