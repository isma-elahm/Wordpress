import {
	createElement,
	Component,
	useEffect,
	useState,
	Fragment,
} from '@wordpress/element'
import Downshift from 'downshift'
import { __ } from 'ct-i18n'
import classnames from 'classnames'

const ListPicker = ({ listId, provider, apiKey, apiUrl, onChange }) => {
	const [lists, setLists] = useState([])
	const [isLoadingLists, setListsLoading] = useState(false)

	let [{ controller }, setAbortState] = useState({
		controller: null,
	})

	const maybeFetchLists = async () => {
		if (controller) {
			controller.abort()
		}

		setListsLoading(true)

		if ('AbortController' in window) {
			controller = new AbortController()

			setAbortState({
				controller,
			})
		}

		const body = new FormData()

		body.append('api_key', apiKey)
		body.append('api_url', apiUrl)
		body.append('provider', provider)
		body.append(
			'action',
			'blocksy_ext_newsletter_subscribe_maybe_get_lists'
		)

		body.append('nonce', ctDashboardLocalizations.dashboard_actions_nonce)

		try {
			const response = await fetch(ctDashboardLocalizations.ajax_url, {
				method: 'POST',
				signal: controller.signal,
				body,
			})

			if (response.status === 200) {
				const body = await response.json()

				if (body.success) {
					if (body.data.result !== 'api_key_invalid') {
						setListsLoading(false)
						setLists(
							body.data.result.map((list) => ({
								...list,
								id: list.id.toString(),
							}))
						)

						return
					}
				}
			}
		} catch (e) {}

		setLists([])
		setListsLoading(false)
	}

	useEffect(() => {
		if (!apiKey && !['mailpoet', 'fluentcrm'].includes(provider)) {
			setLists([])
			return
		}

		maybeFetchLists()
	}, [provider, apiKey, apiUrl])

	return lists.length === 0 ? (
		<div className={classnames('ct-select-input', 'ct-no-results')}>
			<input
				disabled
				placeholder={
					isLoadingLists ? __('Fetching...', 'blocksy-companion') : ''
				}
			/>
		</div>
	) : (
		<Downshift
			selectedItem={lists.find(({ id }) => id === listId) ? listId : ''}
			onChange={(selection) => onChange(selection)}
			itemToString={(item) =>
				item ? (lists.find(({ id }) => id === item) || {}).name : ''
			}>
			{({
				getInputProps,
				getItemProps,
				getLabelProps,
				getMenuProps,
				isOpen,
				inputValue,
				highlightedIndex,
				selectedItem,
				openMenu,
			}) => (
				<div className="ct-select-input">
					<input
						{...getInputProps({
							onFocus: () => openMenu(),
							onClick: () => openMenu(),
						})}
						placeholder={__('Select list...', 'blocksy-companion')}
						readOnly
					/>

					{isOpen && (
						<div
							{...getMenuProps({
								className: 'ct-select-dropdown',
							})}>
							{lists.map((item, index) => (
								<div
									{...getItemProps({
										key: item.id,
										index,
										item: item.id,
										className: classnames(
											'ct-select-dropdown-item',
											{
												active:
													highlightedIndex === index,
												selected:
													selectedItem === item.id,
											}
										),
									})}>
									{item.name}
								</div>
							))}
						</div>
					)}
				</div>
			)}
		</Downshift>
	)
}

export default ListPicker
