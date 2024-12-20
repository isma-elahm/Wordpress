import ctEvents from 'ct-events'

import { typographyOption } from '../../../../static/js/customizer/sync/variables/typography'
import { updateAndSaveEl } from '../../../../static/js/customizer/sync'
import {
	getRootSelectorFor,
	assembleSelector,
	withKeys,
	mutateSelector,
	getSkipRuleKeyword,
} from '../../../../static/js/customizer/sync/helpers'

export const handleMenuVariables = ({ itemId, values }) => {
	const header_menu_type = values.header_menu_type || 'type-1'

	const allSkipped = {
		default: {
			color: getSkipRuleKeyword('DEFAULT'),
		},
		hover: {
			color: getSkipRuleKeyword('DEFAULT'),
		},
		active: {
			color: getSkipRuleKeyword('DEFAULT'),
		},
		'hover-type-3': {
			color: getSkipRuleKeyword('DEFAULT'),
		},
		'active-type-3': {
			color: getSkipRuleKeyword('DEFAULT'),
		},
	}

	const valuesDefaults = {
		menuFontColor: {
			default: { color: 'var(--theme-text-color)' },
			hover: {
				color: getSkipRuleKeyword('DEFAULT'),
			},
			active: {
				color: getSkipRuleKeyword('DEFAULT'),
			},
			'hover-type-3': { color: '#ffffff' },
			'active-type-3': {
				color: getSkipRuleKeyword('DEFAULT'),
			},
		},

		transparentMenuFontColor: allSkipped,
		stickyMenuFontColor: allSkipped,
	}

	const extractKeyWithDefault = (key) => (values) => {
		return values[key] || valuesDefaults[key]
	}

	const menuFontColor = [
		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '> ul > li > a',
				})
			),
			variable: 'theme-link-initial-color',
			type: 'color:default',

			fullValue: true,
			extractValue: extractKeyWithDefault('menuFontColor'),
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '> ul > li > a',
				})
			),
			variable: 'theme-link-hover-color',
			type:
				header_menu_type === 'type-3'
					? 'color:hover-type-3'
					: 'color:hover',

			fullValue: true,
			extractValue: extractKeyWithDefault('menuFontColor'),
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '> ul > li > a',
				})
			),
			variable: 'theme-link-active-color',
			type:
				header_menu_type === 'type-3'
					? 'color:active-type-3'
					: 'color:active',

			fullValue: true,
			extractValue: extractKeyWithDefault('menuFontColor'),
		},
	]

	const transparentMenuFontColor = [
		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-transparent-row="yes"]',
				})
			),
			variable: 'theme-link-initial-color',
			type: 'color:default',

			fullValue: true,
			extractValue: extractKeyWithDefault('transparentMenuFontColor'),
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-transparent-row="yes"]',
				})
			),
			variable: 'theme-link-hover-color',
			type:
				header_menu_type === 'type-3'
					? 'color:hover-type-3'
					: 'color:hover',

			fullValue: true,
			extractValue: extractKeyWithDefault('transparentMenuFontColor'),
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-transparent-row="yes"]',
				})
			),
			variable: 'theme-link-active-color',
			type:
				header_menu_type === 'type-3'
					? 'color:active-type-3'
					: 'color:active',

			fullValue: true,
			extractValue: extractKeyWithDefault('transparentMenuFontColor'),
		},
	]

	const stickyMenuFontColor = [
		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-sticky*="yes"]',
				})
			),
			variable: 'theme-link-initial-color',
			type: 'color:default',

			fullValue: true,

			extractValue: extractKeyWithDefault('stickyMenuFontColor'),
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-sticky*="yes"]',
				})
			),
			variable: 'theme-link-hover-color',
			type:
				header_menu_type === 'type-3'
					? 'color:hover-type-3'
					: 'color:hover',

			fullValue: true,
			extractValue: extractKeyWithDefault('stickyMenuFontColor'),
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-sticky*="yes"]',
				})
			),
			variable: 'theme-link-active-color',
			type:
				header_menu_type === 'type-3'
					? 'color:active-type-3'
					: 'color:active',

			fullValue: true,

			extractValue: extractKeyWithDefault('stickyMenuFontColor'),
		},
	]

	return {
		headerMenuItemsSpacing: {
			selector: assembleSelector(getRootSelectorFor({ itemId })),
			variable: 'menu-items-spacing',
			unit: 'px',
		},

		headerMenuItemsGap: {
			selector: assembleSelector(getRootSelectorFor({ itemId })),
			variable: 'menu-items-gap',
			unit: 'px',
		},

		headerMenuItemsHeight: {
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '> ul > li > a',
				})
			),
			variable: 'menu-item-height',
			unit: '%',
		},

		...typographyOption({
			id: 'headerMenuFont',

			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '> ul > li > a',
				})
			),
		}),

		dropdownTopOffset: {
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu',
				})
			),
			variable: 'dropdown-top-offset',
			unit: 'px',
		},

		stickyStateDropdownTopOffset: {
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.sub-menu',
					}),
					operation: 'between',
					to_add: '[data-sticky*="yes"]',
				})
			),
			variable: 'sticky-state-dropdown-top-offset',
			unit: 'px',
		},

		dropdown_horizontal_offset: {
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu',
				})
			),
			variable: 'dropdown-horizontal-offset',
			unit: 'px',
		},

		dropdownMenuWidth: {
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu',
				})
			),
			variable: 'dropdown-width',
			unit: 'px',
		},

		dropdownItemsSpacing: {
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu',
				})
			),
			variable: 'dropdown-items-spacing',
			unit: 'px',
		},

		...typographyOption({
			id: 'headerDropdownFont',

			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu .ct-menu-link',
				})
			),
		}),

		...withKeys(
			['headerDropdownDivider', 'dropdown_items_type'],
			[
				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu',
						})
					),
					fullValue: true,
					extractValue: ({ headerDropdownDivider }) => {
						return headerDropdownDivider
					},
					variable: 'dropdown-divider',
					type: 'border',
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu',
						})
					),
					fullValue: true,
					extractValue: ({
						dropdown_items_type,
						headerDropdownDivider,
					}) => {
						if (dropdown_items_type !== 'padded') {
							return 'CT_CSS_SKIP_RULE'
						}

						return headerDropdownDivider['style'] !== 'none'
							? '1'
							: '0'
					},
					unit: '',
					variable: 'has-divider',
				},
			]
		),

		headerMenuMargin: {
			selector: assembleSelector(getRootSelectorFor({ itemId })),
			type: 'spacing',
			variable: 'margin',
			responsive: true,
			important: true,
		},

		headerToplevelBorderRadius: {
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '> ul > li > a',
				})
			),
			type: 'spacing',
			variable: 'menu-item-radius',
			responsive: true,
		},

		headerDropdownShadow: {
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu',
				})
			),
			type: 'box-shadow',
			variable: 'theme-box-shadow',
			responsive: true,
		},

		headerDropdownRadius: {
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu',
				})
			),
			type: 'spacing',
			variable: 'theme-border-radius',
			responsive: true,
		},

		header_menu_type: [
			...menuFontColor,

			...(values.stickyMenuFontColor ? stickyMenuFontColor : []),

			...(values.transparentMenuFontColor
				? transparentMenuFontColor
				: []),
		],

		// default state
		menuFontColor,

		menuIndicatorColor: [
			{
				selector: assembleSelector(getRootSelectorFor({ itemId })),
				variable: 'menu-indicator-hover-color',
				type: 'color:hover',
				responsive: true,
			},

			{
				selector: assembleSelector(getRootSelectorFor({ itemId })),
				variable: 'menu-indicator-active-color',
				type: 'color:active',
				responsive: true,
			},
		],

		headerDropdownFontColor: [
			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.sub-menu .ct-menu-link',
					})
				),
				variable: 'theme-link-initial-color',
				type: 'color:default',
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.sub-menu .ct-menu-link',
					})
				),
				variable: 'theme-link-hover-color',
				type: 'color:hover',
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.sub-menu .ct-menu-link',
					})
				),
				variable: 'theme-link-active-color',
				type: 'color:active',
			},
		],

		headerDropdownBackground: [
			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.sub-menu',
					})
				),
				variable: 'dropdown-background-color',
				type: 'color:default',
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.sub-menu',
					})
				),
				variable: 'dropdown-background-hover-color',
				type: 'color:hover',
			},
		],

		// transparent state
		transparentMenuFontColor,

		transparentMenuIndicatorColor: [
			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'between',
						to_add: '[data-transparent-row="yes"]',
					})
				),

				variable: 'menu-indicator-hover-color',
				type: 'color:hover',
				responsive: true,
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'between',
						to_add: '[data-transparent-row="yes"]',
					})
				),

				variable: 'menu-indicator-active-color',
				type: 'color:active',
				responsive: true,
			},
		],

		transparentHeaderDropdownFontColor: [
			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu .ct-menu-link',
						}),
						operation: 'between',
						to_add: '[data-transparent-row="yes"]',
					})
				),
				variable: 'theme-link-initial-color',
				type: 'color:default',
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu .ct-menu-link',
						}),
						operation: 'between',
						to_add: '[data-transparent-row="yes"]',
					})
				),
				variable: 'theme-link-hover-color',
				type: 'color:hover',
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu .ct-menu-link',
						}),
						operation: 'between',
						to_add: '[data-transparent-row="yes"]',
					})
				),
				variable: 'theme-link-active-color',
				type: 'color:active',
			},
		],

		transparentHeaderDropdownBackground: [
			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu',
						}),
						operation: 'between',
						to_add: '[data-transparent-row="yes"]',
					})
				),
				variable: 'dropdown-background-color',
				type: 'color:default',
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu',
						}),
						operation: 'between',
						to_add: '[data-transparent-row="yes"]',
					})
				),
				variable: 'dropdown-background-hover-color',
				type: 'color:hover',
			},
		],

		// sticky state
		stickyMenuFontColor,

		stickyMenuIndicatorColor: [
			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'between',
						to_add: '[data-sticky*="yes"]',
					})
				),
				variable: 'menu-indicator-hover-color',
				type: 'color:hover',
				responsive: true,
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'between',
						to_add: '[data-sticky*="yes"]',
					})
				),
				variable: 'menu-indicator-active-color',
				type: 'color:active',
				responsive: true,
			},
		],

		stickyHeaderDropdownFontColor: [
			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu .ct-menu-link',
						}),
						operation: 'between',
						to_add: '[data-sticky*="yes"]',
					})
				),
				variable: 'theme-link-initial-color',
				type: 'color:default',
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu .ct-menu-link',
						}),
						operation: 'between',
						to_add: '[data-sticky*="yes"]',
					})
				),
				variable: 'theme-link-hover-color',
				type: 'color:hover',
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu .ct-menu-link',
						}),
						operation: 'between',
						to_add: '[data-sticky*="yes"]',
					})
				),
				variable: 'theme-link-active-color',
				type: 'color:active',
			},
		],

		stickyHeaderDropdownBackground: [
			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu',
						}),
						operation: 'between',
						to_add: '[data-sticky*="yes"]',
					})
				),
				variable: 'dropdown-background-color',
				type: 'color:default',
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.sub-menu',
						}),
						operation: 'between',
						to_add: '[data-sticky*="yes"]',
					})
				),
				variable: 'dropdown-background-hover-color',
				type: 'color:hover',
			},
		],
	}
}

export const handleMenuOptions = ({
	selector,
	changeDescriptor: { optionId, optionValue, values },
}) => {
	if (
		optionId === 'header_menu_type' ||
		optionId === 'menu_indicator_effect'
	) {
		updateAndSaveEl(selector, (el) => {
			el.dataset.menu = `${values.header_menu_type}${
				values.header_menu_type === 'type-2'
					? `:${values.menu_indicator_effect}`
					: ``
			}`
		})
	}

	if (optionId === 'headerMenuItemsSpacing') {
		ctEvents.trigger('ct:header:update')
		ctEvents.trigger('ct:header:refresh-menu-submenus')
	}

	if (
		optionId === 'dropdown_animation' ||
		optionId === 'dropdown_items_type'
	) {
		const {
			dropdown_animation = 'type-1',
			dropdown_items_type = 'simple',
		} = values

		updateAndSaveEl(
			selector,
			(el) =>
				(el.dataset.dropdown = `${dropdown_animation}:${dropdown_items_type}`)
		)
	}
}

ctEvents.on('ct:header:sync:item:menu', (changeDescriptor) => {
	const selector = '.header-menu-1'
	handleMenuOptions({ selector, changeDescriptor })
})

ctEvents.on(
	'ct:header:sync:collect-variable-descriptors',
	(variableDescriptors) => {
		variableDescriptors['menu'] = handleMenuVariables
	}
)
