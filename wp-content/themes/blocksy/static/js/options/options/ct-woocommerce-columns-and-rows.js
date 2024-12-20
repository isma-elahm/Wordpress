import {
	createElement,
	Component,
	useState,
	Fragment,
} from '@wordpress/element'
import cls from 'classnames'
import { __, sprintf } from 'ct-i18n'
import NumberOption from './ct-number'
import classnames from 'classnames'

const WooColumnsAndRows = ({
	onChange,
	value,
	option,
	option: { columns_id, rows_id },
	device,
	onChangeFor,
	values,
	values: { woocommerce_catalog_columns, woocommerce_catalog_rows },
	liftedOptionStateDescriptor,
}) => {
	const rowsValue = rows_id ? values[rows_id] : woocommerce_catalog_rows

	return (
		<div
			className={classnames('ct-woo-columns-and-rows', {})}
			{...(device !== 'desktop' ? { 'data-disabled-last': '' } : {})}>
			<div>
				<NumberOption
					liftedOptionStateDescriptor={liftedOptionStateDescriptor}
					option={{
						...option,
						attr: {
							...(option.attr || {}),
							'data-width': 'full',
						},
					}}
					value={
						!columns_id && device === 'desktop'
							? woocommerce_catalog_columns
							: value
					}
					onChange={(val) => {
						device === 'desktop' && !columns_id
							? onChangeFor('woocommerce_catalog_columns', val)
							: onChange(val)
					}}
				/>
				<p className="ct-option-description">
					{__('Number of columns', 'blocksy')}
				</p>
			</div>

			<div>
				<NumberOption
					liftedOptionStateDescriptor={liftedOptionStateDescriptor}
					option={{
						min: 1,
						max: 200,
						responsive: false,
						value: 4,
						attr: {
							'data-width': 'full',
						},
					}}
					value={device === 'desktop' ? rowsValue : 'auto'}
					onChange={(val) => {
						device === 'desktop' &&
							onChangeFor(
								rows_id || 'woocommerce_catalog_rows',
								val
							)

						if (wp.customize && wp.customize.previewer) {
							wp.customize.previewer.send(
								'ct:sync:refresh_partial',
								{
									id: rows_id || 'woocommerce_catalog_rows',
								}
							)
						}
					}}
				/>
				<p className="ct-option-description">
					{__('Number of rows', 'blocksy')}
				</p>
			</div>
		</div>
	)
}

WooColumnsAndRows.renderingConfig = {
	getValueForRevert: ({
		value,
		values: { woocommerce_catalog_columns, woocommerce_catalog_rows },
		option,
		option: { columns_id, rows_id },
		values,
		device,
	}) => {
		const rowsValue = rows_id ? values[rows_id] : woocommerce_catalog_rows
		const columnsValue = columns_id
			? values[columns_id]
			: woocommerce_catalog_columns

		let myResult = {
			...value,
			woocommerce_catalog_columns: columnsValue,
			woocommerce_catalog_rows: rowsValue,
		}

		return myResult
	},

	computeOptionValue: (v, { option, values }) => {
		let result = {
			...v,
			woocommerce_catalog_columns: option.columns_value || 4,
			woocommerce_catalog_rows: option.rows_value || 4,
		}

		return result
	},

	performRevert: ({ onChangeFor, option }) => {
		onChangeFor(
			option.columns_id || 'woocommerce_catalog_columns',
			option.columns_value || 4
		)
		onChangeFor(
			option.rows_id || 'woocommerce_catalog_rows',
			option.rows_value || 4
		)
	},
}

export default WooColumnsAndRows
