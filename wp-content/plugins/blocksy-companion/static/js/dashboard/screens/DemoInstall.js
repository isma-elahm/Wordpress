import {
	createElement,
	Component,
	useEffect,
	useState,
	useMemo,
	createContext,
	Fragment,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import classnames from 'classnames'
import useActivationAction from '../helpers/useActivationAction'
import { Transition, animated } from 'blocksy-options'
import DemosList from './DemoInstall/DemosList'
import DemoToInstall from './DemoInstall/DemoToInstall'

import useDemoListFilters from './DemoInstall/filters/useDemoListFilters'

import SubmitSupport from '../helpers/SubmitSupport'

import NoLicense from '../NoLicense'

import { getStarterSitesStatus } from '../helpers/starter-sites'

export const DemosContext = createContext({
	demos: [],
})

let demos_cache = null
let demos_error_cache = null

const DemoInstall = ({ children, path, location }) => {
	const [isLoading, setIsLoading] = useState(!demos_cache)
	const [demos_list, setDemosList] = useState(demos_cache || [])
	const [pluginsStatus, setPluginsStatus] = useState(
		ctDashboardLocalizations.plugin_data.active_plugins
	)
	const [currentDemo, setCurrentDemo] = useState(null)
	const [currentlyInstalledDemo, setCurrentlyInstalledDemo] = useState(
		ctDashboardLocalizations.plugin_data.current_installed_demo
	)

	const filtersPayload = useDemoListFilters({
		demos_list,
	})

	const [forceEmptyDemos, setForceEmptyDemos] = useState(false)

	const [demo_error, setDemoError] = useState({
		isError: false,
		message: '',
		// generic | remote_fetch_failed | ajax_request_failed
		reason: 'generic',
	})

	const [demoConfiguration, setDemoConfiguration] = useState({
		builder: '',
	})

	const [installerBlockingReleased, setInstallerBlockingReleased] =
		useState(false)

	const syncDemos = async (verbose = false) => {
		if (verbose) {
			setIsLoading(true)
		}

		try {
			let demosResponse = await getStarterSitesStatus()

			if (demosResponse.status && demosResponse.status === 511) {
				// This is a special case where the license is invalid
				setForceEmptyDemos(true)
				return
			}

			const data = await demosResponse.json()

			setDemosList(data)
			demos_cache = data
		} catch (response) {
			// Usually this is a network error or some policty blocking
			// external frontend requests
			console.error('Blocksy:Dashboard:DemoInstall:demos_list', {
				response,
				reason: 'frontend_starter_sites_fetch_failed',
			})
		}

		setIsLoading(false)
	}

	useEffect(() => {
		if (ctDashboardLocalizations.plugin_data.demo_install_error) {
			setIsLoading(false)

			setDemoError({
				isError: true,
				message:
					ctDashboardLocalizations.plugin_data.demo_install_error,
				reason: 'generic',
			})
		} else {
			syncDemos(!demos_cache)
		}
	}, [])

	if (forceEmptyDemos) {
		return <NoLicense />
	}

	return (
		<div className="ct-demos-list-container">
			<Transition
				items={isLoading}
				from={{ opacity: 0 }}
				enter={[{ opacity: 1 }]}
				leave={[{ opacity: 0 }]}
				config={(key, phase) => {
					return phase === 'leave'
						? {
								duration: 300,
						  }
						: {
								delay: 300,
								duration: 300,
						  }
				}}>
				{(props, isLoading) => {
					if (isLoading) {
						return (
							<animated.p
								style={props}
								className="ct-loading-text">
								<svg
									width="16"
									height="16"
									viewBox="0 0 100 100">
									<g transform="translate(50,50)">
										<g transform="scale(1)">
											<circle
												cx="0"
												cy="0"
												r="50"
												fill="currentColor"></circle>
											<circle
												cx="0"
												cy="-26"
												r="12"
												fill="#ffffff"
												transform="rotate(161.634)">
												<animateTransform
													attributeName="transform"
													type="rotate"
													calcMode="linear"
													values="0 0 0;360 0 0"
													keyTimes="0;1"
													dur="1s"
													begin="0s"
													repeatCount="indefinite"></animateTransform>
											</circle>
										</g>
									</g>
								</svg>

								{__(
									'Loading Starter Sites...',
									'blocksy-companion'
								)}
							</animated.p>
						)
					}

					if (demo_error.isError) {
						return (
							<animated.div style={props}>
								{demo_error.reason ===
									'ajax_request_failed' && (
									<div
										className="ct-demo-notification"
										dangerouslySetInnerHTML={{
											__html: sprintf(
												__(
													'Your site is misconfigured and AJAX requests are not reaching your backend. Please click %shere%s to find the common causes and possible solutions to this.<br> Error code - %s',
													'blocksy-companion'
												),
												'<a href="https://creativethemes.com/blocksy/docs/troubleshooting/starter-site-common-issues-possible-solutions/" target="_blank">',
												'</a>',
												demo_error.message
											),
										}}
									/>
								)}

								{demo_error.reason ===
									'remote_fetch_failed' && (
									<div
										className="ct-demo-notification"
										dangerouslySetInnerHTML={{
											__html: sprintf(
												__(
													'Failed to retrieve starter sites list.<br> Error code - %s',
													'blocksy-companion'
												),
												demo_error.message
											),
										}}
									/>
								)}

								{demo_error.reason === 'generic' && (
									<div
										className="ct-demo-notification"
										dangerouslySetInnerHTML={{
											__html: demo_error.message,
										}}
									/>
								)}

								<SubmitSupport />
							</animated.div>
						)
					}

					return (
						<animated.div style={props}>
							<Fragment>
								<DemosContext.Provider
									value={{
										currentDemo,

										pluginsStatus,
										setPluginsStatus,

										installerBlockingReleased,
										setInstallerBlockingReleased,
										setCurrentDemo,
										currentlyInstalledDemo,
										setCurrentlyInstalledDemo,

										unfiltered_demos_list:
											filtersPayload.unfiltered_demos_list,
										demos_list: filtersPayload.demos_list,
										filters: filtersPayload.filters,
										setFilters: filtersPayload.setFilters,

										allPlans: filtersPayload.allPlans,
										allCategories:
											filtersPayload.allCategories,
									}}>
									{filtersPayload.display()}
									<DemosList />
									<DemoToInstall />
								</DemosContext.Provider>
								<SubmitSupport />
							</Fragment>
						</animated.div>
					)
				}}
			</Transition>
		</div>
	)
}

export default DemoInstall
