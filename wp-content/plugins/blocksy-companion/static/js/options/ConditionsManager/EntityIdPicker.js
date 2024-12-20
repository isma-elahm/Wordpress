import { createElement, useEffect, useState, useMemo } from '@wordpress/element'
import { Select } from 'blocksy-options'
import { __ } from 'ct-i18n'

import cachedFetch from 'ct-wordpress-helpers/cached-fetch'

const withUniqueIDs = (data) =>
	data.filter(
		(value, index, self) =>
			self.findIndex((m) => m.id === value.id) === index
	)

const EntityIdPicker = ({
	entity,
	placeholder,
	additionOptions = [],
	value,
	postType,
	onChange,
}) => {
	const [allEntities, setAllEntities] = useState([])
	const requestBody = useMemo(() => {
		const requestBody = {}

		if (entity === 'posts') {
			requestBody.post_type = postType
		}

		// if (entity === 'users') {}

		if (entity === 'taxonomies') {
			requestBody.post_type = postType
		}

		return {
			...requestBody,
			...(value ? { alsoInclude: value } : {}),
		}
	}, [entity, postType, value])

	const fetchPosts = (searchQuery = '') => {
		cachedFetch(
			`${wp.ajax.settings.url}?action=blocksy_conditions_get_all_entities`,
			{
				entity,

				...(searchQuery ? { search_query: searchQuery } : {}),
				...requestBody,
			},
			{
				// Abort intermediary requests.
				fetcherName: `conditions-get-all-entities-picker`,
			}
		)
			.then((r) => r.json())
			.then(({ data: { entities } }) => {
				setAllEntities(withUniqueIDs([...entities]))
			})
	}

	useEffect(() => {
		fetchPosts()
	}, [entity, postType])

	return (
		<Select
			option={{
				appendToBody: true,
				defaultToFirstItem: false,
				searchPlaceholder: __(
					'Type to search by ID or title...',
					'blocksy-companion'
				),
				placeholder,
				choices: [
					...additionOptions,
					...allEntities.map((entity) => ({
						key: entity.id,
						value: entity.label,
						...(entity.group ? { group: entity.group } : {}),
					})),
				],
				search: true,
			}}
			value={value}
			onChange={(entity_id) =>
				onChange(
					allEntities.find(({ id }) => id === entity_id) || entity_id
				)
			}
			onInputValueChange={(value) => {
				if (
					allEntities.find(
						({ label, id }) => label === value || id === value
					)
				) {
					return
				}

				fetchPosts(value)
			}}
		/>
	)
}

export default EntityIdPicker
