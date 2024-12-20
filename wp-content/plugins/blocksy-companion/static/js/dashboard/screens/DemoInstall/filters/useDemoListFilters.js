import { createElement, useState } from '@wordpress/element'

import DemoListFilters from './DemoListFilters'

const allPlans = [
	{
		id: 'personal',
		title: 'Personal',
	},

	{
		id: 'professional',
		title: 'Professional',
	},

	{
		id: 'agency',
		title: 'Agency',
	},

	{
		id: 'personal_v2',
		title: 'Personal v2',
	},

	{
		id: 'professional_v2',
		title: 'Professional v2',
	},

	{
		id: 'agency_v2',
		title: 'Agency v2',
	},
]

const allCategories = [
	'Blog',
	'Business',
	'Ecommerce',
	'News',
	'Nonprofit',
	'Personal',
	'Portfolio',
	'Travel',
]

function fuzzysearch(needle, haystack) {
	var hlen = haystack.length
	var nlen = needle.length
	if (nlen > hlen) {
		return false
	}
	if (nlen === hlen) {
		return needle === haystack
	}
	outer: for (var i = 0, j = 0; i < nlen; i++) {
		var nch = needle.charCodeAt(i)
		while (j < hlen) {
			if (haystack.charCodeAt(j++) === nch) {
				continue outer
			}
		}
		return false
	}

	return true
}

const filters = {}

const filterDemos = (demos_list, filters) => {
	let filtered_demos_list = demos_list

	if (filters.plan !== 'all') {
		filtered_demos_list = filtered_demos_list.filter((demo) => {
			if (
				(filters.plan === 'free' && demo.is_pro) ||
				(filters.plan === 'pro' && !demo.is_pro)
			) {
				return false
			}

			return true
		})
	}

	if (filters.category !== 'all') {
		filtered_demos_list = filtered_demos_list.filter((demo) => {
			if (!demo.categories) {
				return false
			}

			return demo.categories.includes(filters.category)
		})
	}

	if (filters.search) {
		filtered_demos_list = filtered_demos_list.filter((demo) => {
			if (
				demo.keywords &&
				demo.keywords
					.split(',')
					.some((keyword) =>
						fuzzysearch(
							filters.search.toLowerCase(),
							keyword.trim().toLowerCase()
						)
					)
			) {
				return true
			}

			return fuzzysearch(
				filters.search.toLowerCase(),
				demo.name.toLowerCase()
			)
		})
	}

	if (filters.builder !== 'all') {
		filtered_demos_list = filtered_demos_list.filter((demo) => {
			const demoWithSuchBuilder = demos_list.find(
				(d) =>
					d.name === demo.name &&
					(d.builder || 'gutenberg') === filters.builder
			)

			return !!demoWithSuchBuilder
		})
	}

	return filtered_demos_list
}

const useDemoListFilters = ({ demos_list }) => {
	const [filters, setFilters] = useState({
		search: '',
		category: 'all',
		builder: 'all',
		plan: 'all',
	})

	return {
		filters,
		setFilters,

		unfiltered_demos_list: demos_list,

		demos_list: filterDemos(
			demos_list.filter(
				(demo) => !demo.dev_v2 || ct_localizations.is_dev_mode
			),

			filters
		),

		allPlans,
		allCategories,

		display: () => <DemoListFilters />,
	}
}

export default useDemoListFilters
