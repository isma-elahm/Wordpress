import {
	createElement,
	memo,
	useState,
	RawHTML,
	Fragment,
} from '@wordpress/element'

import { syncHelpers } from 'blocksy-options'
import nanoid from 'nanoid'

export const getStylesForBlock = (variables, cssURLs = []) => {
	let result = ''

	const cacheId = nanoid()
	const commonArgs = {
		cacheId,
		initialStyleTagsDescriptor: [
			{
				readStyles: () => {
					return ''
				},

				persistStyles: (newCss) => {
					result = newCss
				},
			},
		],
	}

	const astDescriptor = syncHelpers.getStyleTagsWithAst(commonArgs)

	variables.map((variableDescriptor) => {
		let ast = syncHelpers.getUpdateAstsForStyleDescriptor({
			variableDescriptor: variableDescriptor.variables,
			value: variableDescriptor.value,
			fullValue: {},

			...commonArgs,
		})

		syncHelpers.persistNewAsts(cacheId, ast)
	})

	let styles = result ? <style>{result}</style> : null

	cssURLs = [ct_localizations.backend_dynamic_styles_urls.flexy]

	return (
		<Fragment>
			{styles}

			{cssURLs.map((url) => {
				return <link key={url} rel="stylesheet" href={url} />
			})}
		</Fragment>
	)
}
