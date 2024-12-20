import { cloneElement, createElement, useContext } from '@wordpress/element'
import { DropdownMenu, MenuItem } from '@wordpress/components'

import { __ } from 'ct-i18n'
import { DemosContext } from '../../DemoInstall'

import { check } from '@wordpress/icons'
import SiteExport from '../../SiteExport'

import { getPluginsMap } from '../Wizzard/Plugins'

const humanizePlans = {
	all: __('All Plans', 'blocksy-companion'),
	free: __('Free', 'blocksy-companion'),
	pro: __('Pro', 'blocksy-companion'),
}

const DemoListFilters = () => {
	const {
		unfiltered_demos_list,
		filters,
		setFilters,
		allPlans,
		allCategories,
	} = useContext(DemosContext)

	const allBuilders = unfiltered_demos_list.reduce((acc, demo) => {
		let builder = 'gutenberg'

		if (demo.builder) {
			builder = demo.builder
		}

		if (!acc.includes(builder)) {
			acc.push(builder)
		}

		return acc
	}, [])

	return (
		<div className="ct-demo-filters">
			<DropdownMenu
				className="ct-filter-trigger-categories"
				menuProps={{
					className: 'ct-filter-dropdown-categories',
				}}
				icon={
					<svg
						aria-hidden="true"
						viewBox="0 0 24 24"
						fillRule="evenodd"
						fill="currentColor">
						<path d="M18 5.5h-3c-.3 0-.5.2-.5.5v3c0 .3.2.5.5.5h3c.3 0 .5-.2.5-.5V6c0-.3-.2-.5-.5-.5zm2 .5c0-1.1-.9-2-2-2h-3c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h3c1.1 0 2-.9 2-2V6zM9 14.5H6c-.3 0-.5.2-.5.5v3c0 .3.2.5.5.5h3c.3 0 .5-.2.5-.5v-3c0-.3-.2-.5-.5-.5zm2 .5c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h3c1.1 0 2-.9 2-2v-3zm4-.5h3c.3 0 .5.2.5.5v3c0 .3-.2.5-.5.5h-3c-.3 0-.5-.2-.5-.5v-3c0-.3.2-.5.5-.5zm3-1.5c1.1 0 2 .9 2 2v3c0 1.1-.9 2-2 2h-3c-1.1 0-2-.9-2-2v-3c0-1.1.9-2 2-2h3zM9 5.5H6c-.3 0-.5.2-.5.5v3c0 .3.2.5.5.5h3c.3 0 .5-.2.5-.5V6c0-.3-.2-.5-.5-.5zm2 .5c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h3c1.1 0 2-.9 2-2V6z" />
					</svg>
				}
				label={
					filters.category === 'all'
						? __('All Categories', 'blocksy-companion')
						: filters.category
				}
				text={
					filters.category === 'all'
						? __('All Categories', 'blocksy-companion')
						: filters.category
				}>
				{() =>
					['all', ...allCategories].map((category) => {
						const isSelected = filters.category === category

						return (
							<MenuItem
								key={category}
								icon={
									isSelected
										? cloneElement(check, {
												width: 24,
												height: 24,
										  })
										: null
								}
								isSelected={isSelected}
								onClick={() => {
									setFilters({ ...filters, category })
								}}>
								{category === 'all'
									? __('All Categories', 'blocksy-companion')
									: category}
							</MenuItem>
						)
					})
				}
			</DropdownMenu>

			<DropdownMenu
				className="ct-filter-trigger-plans"
				menuProps={{
					className: 'ct-filter-dropdown-plans',
				}}
				icon={
					<svg
						aria-hidden="true"
						viewBox="0 0 24 24"
						fillRule="evenodd"
						fill="currentColor">
						<path d="m17.3 19.8-5.2-2.7h-.2l-5.2 2.7 1-5.8c0-.1 0-.2-.1-.2l-4.2-4L9.2 9c.1 0 .2-.1.2-.1L12 3.6l2.6 5.2c0 .1.1.1.2.1l5.8.8-4.2 4.1c-.1.1-.1.1-.1.2l1 5.8zM12 15.6c.3 0 .6.1.8.2l2.5 1.3-.5-2.8c-.1-.6.1-1.1.5-1.5l2-2-2.8-.4c-.6-.1-1.1-.4-1.3-1L12 6.9l-1.2 2.5c-.3.5-.7.9-1.3 1l-2.8.4 2 2c.4.4.6 1 .5 1.5l-.5 2.8 2.5-1.3c.2-.1.5-.2.8-.2z" />
					</svg>
				}
				label={humanizePlans[filters.plan]}
				text={humanizePlans[filters.plan]}>
				{() =>
					['all', 'free', 'pro'].map((plan) => {
						const isSelected = filters.plan === plan

						return (
							<MenuItem
								key={plan}
								icon={
									isSelected
										? cloneElement(check, {
												width: 24,
												height: 24,
										  })
										: null
								}
								isSelected={isSelected}
								onClick={() => {
									setFilters({ ...filters, plan })
								}}>
								{humanizePlans[plan]}
							</MenuItem>
						)
					})
				}
			</DropdownMenu>

			<DropdownMenu
				className="ct-filter-trigger-builders"
				menuProps={{
					className: 'ct-filter-dropdown-builders',
				}}
				icon={
					<svg
						aria-hidden="true"
						viewBox="0 0 24 24"
						fillRule="evenodd"
						fill="currentColor">
						<path d="M18 4H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM6 5.5h12c.3 0 .5.2.5.5v3h-13V6c0-.3.2-.5.5-.5zM5.5 18v-7.5h3v8H6c-.3 0-.5-.2-.5-.5zm12.5.5h-8v-8h8.5V18c0 .3-.2.5-.5.5z" />
					</svg>
				}
				label={
					filters.builder === 'all'
						? __('All Builders', 'blocksy-companion')
						: getPluginsMap()[filters.builder]
				}
				text={
					filters.builder === 'all'
						? __('All Builders', 'blocksy-companion')
						: getPluginsMap()[filters.builder]
				}>
				{() =>
					['all', ...allBuilders].map((builder) => {
						const isSelected = filters.builder === builder

						return (
							<MenuItem
								key={builder}
								icon={
									isSelected
										? cloneElement(check, {
												width: 24,
												height: 24,
										  })
										: null
								}
								isSelected={isSelected}
								onClick={() => {
									setFilters({ ...filters, builder })
								}}>
								{builder === 'all'
									? __('All Builders', 'blocksy-companion')
									: getPluginsMap()[builder]}
							</MenuItem>
						)
					})
				}
			</DropdownMenu>

			{ct_localizations.is_dev_mode && (
				<SiteExport allPlans={allPlans} allCategories={allCategories} />
			)}

			<div className="ct-filter-search">
				<svg
					aria-hidden="true"
					width="13"
					height="13"
					viewBox="0 0 15 15"
					fill="currentColor">
					<path d="M14.8,13.7L12,11c0.9-1.2,1.5-2.6,1.5-4.2c0-3.7-3-6.8-6.8-6.8S0,3,0,6.8s3,6.8,6.8,6.8c1.6,0,3.1-0.6,4.2-1.5l2.8,2.8c0.1,0.1,0.3,0.2,0.5,0.2s0.4-0.1,0.5-0.2C15.1,14.5,15.1,14,14.8,13.7z M1.5,6.8c0-2.9,2.4-5.2,5.2-5.2S12,3.9,12,6.8S9.6,12,6.8,12S1.5,9.6,1.5,6.8z"></path>
				</svg>

				<input
					type="text"
					placeholder={__(
						'Search for a starter site...',
						'blocksy-companion'
					)}
					value={filters.search}
					onChange={(event) => {
						setFilters({ ...filters, search: event.target.value })
					}}
				/>
			</div>
		</div>
	)
}

export default DemoListFilters
