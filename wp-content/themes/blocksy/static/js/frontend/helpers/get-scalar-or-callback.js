export const getScalarOrCallback = (scalarOrFn) => {
	if (scalarOrFn && {}.toString.call(scalarOrFn) === '[object Function]') {
		return scalarOrFn()
	}

	return scalarOrFn
}
