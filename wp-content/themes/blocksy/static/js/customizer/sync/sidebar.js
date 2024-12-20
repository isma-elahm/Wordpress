import {
	changeTagName,
	getOptionFor,
	responsiveClassesFor,
	watchOptionsWithPrefix,
} from './helpers'

watchOptionsWithPrefix({
	getOptionsForPrefix: ({ prefix }) => [
		`${prefix}_sidebar_position`,
		'separated_widgets',
		'widgets_title_wrapper',
		'sidebar_type',
		'sidebar_visibility',
		'sidebar_stick_behavior',
	],

	render: ({ prefix }) => {
		if (getOptionFor('sidebar_position', prefix)) {
			const sidebarContainer = document.querySelector('[data-sidebar]')

			if (sidebarContainer) {
				sidebarContainer.dataset.sidebar = getOptionFor(
					'sidebar_position',
					prefix
				)
			}
		}

		const sidebar = document.querySelector('#sidebar .ct-sidebar')

		if (!sidebar) return

		sidebar.parentNode.dataset.type = getOptionFor('sidebar_type')

		sidebar.removeAttribute('data-widgets')

		if (
			getOptionFor('separated_widgets') === 'yes' &&
			getOptionFor('sidebar_type') === 'type-2'
		) {
			document.querySelector('.ct-sidebar').dataset.widgets = 'separated'
		}

		sidebar.removeAttribute('data-sticky')

		if (getOptionFor('has_sticky_sidebar') === 'yes') {
			sidebar.dataset.sticky =
				getOptionFor('sidebar_stick_behavior') === 'sidebar'
					? 'sidebar'
					: 'widgets'
		}

		;[...document.querySelectorAll('.widget-title')].map((el) =>
			changeTagName(el, wp.customize('widgets_title_wrapper')())
		)

		responsiveClassesFor('sidebar_visibility', sidebar.parentNode)
		window.ctEvents.trigger('blocksy:frontend:init')
	},
})
