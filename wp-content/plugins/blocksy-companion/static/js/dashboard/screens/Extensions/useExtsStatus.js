import {
	createElement,
	Component,
	useEffect,
	useMemo,
	useState,
	Fragment,
} from '@wordpress/element'

import ctEvents from 'ct-events'
import { flushPermalinks } from '../../../flushPermalinks'

import { getStarterSitesStatus } from '../../helpers/starter-sites'

let exts_status_cache = null
let force_empty_exts_cache = false

export const getRawExtsStatus = () => exts_status_cache || []

const useExtsStatus = () => {
	const [isLoading, setIsLoading] = useState(!exts_status_cache)
	const [exts_status, setExtsStatus] = useState(exts_status_cache || [])

	const [forceEmptyExts, setForceEmptyExts] = useState(force_empty_exts_cache)

	let [{ controller }, setAbortState] = useState({
		controller: null,
	})

	const syncExts = async (args = {}) => {
		let { verbose, extension, extAction } = {
			verbose: false,
			extension: null,
			extAction: null,
			...args,
		}

		if (verbose) {
			setIsLoading(true)
		}

		if (controller) {
			controller.abort()
		}

		try {
			let demosResponse = await getStarterSitesStatus()

			if (demosResponse.status && demosResponse.status === 511) {
				force_empty_exts_cache = true
				setForceEmptyExts(true)
			}
		} catch (response) {}

		if ('AbortController' in window) {
			controller = new AbortController()

			setAbortState({
				controller,
			})
		}

		const response = await fetch(
			`${wp.ajax.settings.url}?action=blocksy_extensions_status&nonce=${ctDashboardLocalizations.dashboard_actions_nonce}`,

			{
				method: 'POST',
				signal: controller.signal,
				...(extension && extAction
					? {
							body: JSON.stringify({
								extension,
								extAction,
							}),
					  }
					: {}),
			}
		)

		if (response.status !== 200) {
			return
		}

		const { success, data } = await response.json()

		if (!success) {
			return
		}

		if (!!extAction?.require_refresh) {
			flushPermalinks()
		}

		setExtsStatus(data)
		exts_status_cache = data

		setIsLoading(false)

		if (extension) {
			return data[extension]
		}

		return data
	}

	useEffect(() => {
		if (!exts_status_cache) {
			syncExts({ verbose: true })
		}

		const cb = () => {
			syncExts()
		}

		ctEvents.on('blocksy_exts_sync_exts', cb)

		return () => {
			ctEvents.off('blocksy_exts_sync_exts', cb)
		}
	}, [])

	return {
		syncExts,
		isLoading,
		exts_status,

		forceEmptyExts,

		setExtsStatus: (cb) => {
			const data = cb(exts_status)
			exts_status_cache = data
			setExtsStatus(cb)
		},
	}
}

export default useExtsStatus
