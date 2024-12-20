import { getSpacingPresetCssVar } from '@wordpress/block-editor'

export const getVariablesDefinitions = ({ attributes, context }) => {
	const { style, layout, verticalAlignment } = attributes
	const { has_slideshow, uniqueId } = context

	const { type: layoutType, columnCount = 3 } = layout || {}
	const isGridLayout = layoutType === 'grid'
	const isManualMode = columnCount !== null

	const isSlideshow = has_slideshow === 'yes'

	let columnsGap = ''

	if (style && style.spacing && style.spacing.blockGap) {
		columnsGap = getSpacingPresetCssVar(style.spacing.blockGap)
	}

	if (!columnsGap) {
		columnsGap = 'CT_CSS_SKIP_RULE'
	}

	let variables = []

	let columns = {
		desktop: 1,
		tablet: 1,
		mobile: 1,
	}

	if (isGridLayout && verticalAlignment) {
		variables.push({
			variables: {
				selector: `[data-id='${uniqueId}']`,
				variable: 'align-items',
			},
			value:
				verticalAlignment === 'top'
					? 'flex-start'
					: verticalAlignment === 'bottom'
					? 'flex-end'
					: 'center',
		})
	}

	if (isGridLayout && verticalAlignment) {
		variables.push({
			variables: {
				selector: `[data-id='${uniqueId}']`,
				variable: 'align-items',
			},
			value:
				verticalAlignment === 'top'
					? 'flex-start'
					: verticalAlignment === 'bottom'
					? 'flex-end'
					: 'center',
		})
	}

	if (isGridLayout && isManualMode) {
		columns = {
			desktop: columnCount,
			tablet: attributes.tabletColumns,
			mobile: attributes.mobileColumns,
		}

		variables.push({
			variables: {
				selector: `[data-id='${uniqueId}']`,
				variable: 'grid-columns-gap',
				unit: '',
			},

			value: columnsGap,
		})

		let gridColumnsWidth = {
			desktop: columnCount,
			tablet: attributes.tabletColumns,
			mobile: attributes.mobileColumns,
		}

		if (isSlideshow) {
			gridColumnsWidth = {
				desktop: `${(100 / columnCount).toFixed(2)}%`,
				tablet: `${(100 / attributes.tabletColumns).toFixed(2)}%`,
				mobile: `${(100 / attributes.mobileColumns).toFixed(2)}%`,
			}
		}

		variables.push({
			variables: {
				selector: `[data-id='${uniqueId}']`,
				variable: 'grid-columns-width',
				unit: '',
				responsive: true,
			},

			value: gridColumnsWidth,
		})
	} else {
		if (!isSlideshow) {
			variables.push({
				variables: {
					selector: `.editor-styles-wrapper [data-id='${uniqueId}'] :where(.is-layout-flow > *)`,
					variable: 'margin-block-end',
					unit: '',
					responsive: true,
				},

				value: columnsGap,
			})
		}
	}

	if (isSlideshow) {
		const selectors = {
			desktop: '',
			tablet: '',
			mobile: '',
		}

		Object.keys(selectors).forEach((device) => {
			selectors[device] = [
				`[data-id='${uniqueId}']`,
				'[data-flexy="no"]',
				`.flexy-item:nth-child(n + ${parseFloat(columns[device]) + 1})`,
			].join(' ')
		})

		variables.push({
			variables: {
				selector: selectors,
				variable: 'height',
				responsive: true,
			},

			value: '1px',
		})
	}

	return variables
}
