export const wpUpdatesAjax = function (action, data) {
	var options = {}

	if (data.success) {
		options.success = data.success
		delete data.success
	}

	if (data.error) {
		options.error = data.error
		delete data.error
	}

	options.data = {
		...data,
	}

	options.data = _.extend(data, {
		action: action,
		_ajax_nonce: ctDashboardLocalizations.updatesNonce,
	})

	return wp.ajax.send(options)
}
