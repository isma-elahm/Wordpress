import {
	createContext,
	createElement,
	useEffect,
	useState,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import { Switch } from 'blocksy-options'

import ConditionsWithRelation from './ConditionsManager/ConditionsWithRelation'

export const ConditionsDataContext = createContext({
	allLanguages: [],
	remoteConditions: [],
})

let allLanguagesCache = []
let remoteConditionsCache = []

const ConditionsManager = ({
	value,
	onChange,
	filter = 'all',
	addConditionButtonLabel,
}) => {
	const [allLanguages, setAllLanguages] = useState(allLanguagesCache)
	const [remoteConditions, setRemoteConditions] = useState(
		remoteConditionsCache
	)

	const [isAdvancedModeInternal, setIsAdvancedMode] = useState('__EMPTY__')

	useEffect(() => {
		fetch(
			`${wp.ajax.settings.url}?action=blc_retrieve_conditions_data&filter=${filter}`,
			{
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
				method: 'POST',
			}
		)
			.then((r) => r.json())
			.then(({ data: { languages, conditions } }) => {
				setAllLanguages(languages)
				allLanguagesCache = languages

				setRemoteConditions(conditions)
				remoteConditionsCache = conditions
			})
	}, [])

	const conditionsListDescriptor = Array.isArray(value)
		? {
				relation: 'OR',
				conditions: value,
		  }
		: value

	let isAdvancedMode =
		isAdvancedModeInternal === '__EMPTY__' ? false : isAdvancedModeInternal

	if (
		conditionsListDescriptor.conditions.find(
			(condition) => condition.relation
		)
	) {
		isAdvancedMode = true
	}

	/*
	const conditionsListDescriptor = {
		relation: 'OR',
		conditions: [
			{
				type: 'include',
				rule: rulesToUse[0].rules[0].id,
				payload: {},
			},
			{
				type: 'include',
				rule: rulesToUse[0].rules[0].id,
				payload: {},
			},
			{
				type: 'include',
				rule: rulesToUse[0].rules[0].id,
				payload: {},
			},

			{
				relation: 'AND',
				conditions: [
					{
						type: 'include',
						rule: rulesToUse[0].rules[0].id,
						payload: {},
					},

					{
						type: 'include',
						rule: rulesToUse[0].rules[0].id,
						payload: {},
					},

					{
						type: 'include',
						rule: rulesToUse[0].rules[0].id,
						payload: {},
					},

					{
						relation: 'OR',
						conditions: [
							{
								type: 'include',
								rule: rulesToUse[0].rules[0].id,
								payload: {},
							},

							{
								type: 'include',
								rule: rulesToUse[0].rules[0].id,
								payload: {},
							},
						],
					},

					{
						type: 'include',
						rule: rulesToUse[0].rules[0].id,
						payload: {},
					},
				],
			},
		],
	}
    */
	// console.log(conditionsListDescriptor)

	return (
		<ConditionsDataContext.Provider
			value={{
				allLanguages,
				remoteConditions,
				isAdvancedMode,
			}}>
			<div className="ct-display-conditions">
				<ConditionsWithRelation
					conditionsListDescriptor={conditionsListDescriptor}
					onChange={(conditions) => {
						onChange({
							...conditionsListDescriptor,
							conditions,
						})
					}}
				/>

				<div className="ct-conditions-actions">
					<button
						type="button"
						className="button add-condition"
						onClick={(e) => {
							e.preventDefault()

							if (remoteConditions.length === 0) {
								return
							}

							onChange({
								...conditionsListDescriptor,
								conditions: [
									...conditionsListDescriptor.conditions,
									{
										type: 'include',
										rule: remoteConditions[0].rules[0].id,
										payload: {},
									},
								],
							})
						}}>
						{addConditionButtonLabel}
					</button>

					{(conditionsListDescriptor.conditions.length > 1 ||
						conditionsListDescriptor.conditions.find(
							(condition) => condition.relation
						)) && (
						<span>
							{__('Advanced Mode', 'blocksy-companion')}
							<Switch
								value={isAdvancedMode}
								onChange={(value) => {
									if (
										conditionsListDescriptor.conditions.find(
											(condition) => condition.relation
										)
									) {
										onChange({
											...conditionsListDescriptor,
											conditions:
												conditionsListDescriptor.conditions.reduce(
													(acc, condition) => {
														return [
															...acc,
															...(condition.conditions
																? condition.conditions
																: [condition]),
														]
													},
													[]
												),
										})
									}

									setIsAdvancedMode(value)
								}}
								option={{
									behavior: 'boolean',
								}}
							/>
						</span>
					)}
				</div>
			</div>
		</ConditionsDataContext.Provider>
	)
}

export default ConditionsManager
