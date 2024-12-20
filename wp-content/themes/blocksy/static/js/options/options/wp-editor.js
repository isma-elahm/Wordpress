import {
	createElement,
	useRef,
	useState,
	useEffect,
	useCallback,
} from '@wordpress/element'
import md5 from 'md5'
import ctEvents from 'ct-events'

const WpEditor = ({ id, value, option, onChange }) => {
	const el = useRef()
	const editor = useRef(null)
	const [editorId, _] = useState(
		`${id}-${md5(
			Math.random() + '-' + Math.random() + '-' + Math.random()
		)}`
	)

	const correctEditor = () => wp.oldEditor || wp.editor

	const listener = useCallback(() => {
		onChange(correctEditor().getContent(editorId))
	}, [editorId])

	const mount = () => {
		if (wp.oldEditor) {
			if (window.tinymce && window.tinymce.editors[editorId]) {
				window.tinymce.editors[editorId].off('input', listener)

				window.tinymce.editors[editorId].off('change', listener)
			}

			correctEditor().remove(editorId)

			correctEditor().initialize(editorId, {
				quicktags: true,
				mediaButtons: true,
				...option,

				...(window.tinymce
					? {
							tinymce: {
								toolbar1:
									'formatselect,styleselect,bold,italic,bullist,numlist,link,alignleft,aligncenter,alignright,wp_adv',
								toolbar2:
									'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',

								...(typeof option.tinymce === 'object'
									? option.tinymce
									: {}),
								style_formats_merge: true,
								style_formats: [],
							},
					  }
					: { tinymce: null }),
			})

			if (window.tinymce) {
				setTimeout(() => {
					const maybeEditor = window.tinymce.editors[editorId]

					if (maybeEditor) {
						maybeEditor.on('input', listener)
						maybeEditor.on('change', listener)
					}
				})
			}
		} else {
			correctEditor().initialize(editorId, {
				quicktags: true,
				mediaButtons: true,
				...option,

				...(window.tinymce
					? {
							tinymce: {
								toolbar1:
									'formatselect,styleselect,bold,italic,bullist,numlist,link,alignleft,aligncenter,alignright,wp_adv',
								toolbar2:
									'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',

								...(typeof option.tinymce === 'object'
									? option.tinymce
									: {}),
								style_formats_merge: true,
								style_formats: [],
							},
					  }
					: { tinymce: null }),
			})

			if (window.tinymce) {
				setTimeout(() => {
					const maybeEditor = window.tinymce.editors[editorId]

					if (maybeEditor) {
						maybeEditor.on('input', listener)
						maybeEditor.on('change', listener)
					}
				})
			}
		}
	}

	const unmount = () => {
		if (window.tinymce && window.tinymce.editors[editorId]) {
			window.tinymce.editors[editorId].off('input', listener)
			window.tinymce.editors[editorId].off('change', listener)
		}

		correctEditor().remove(editorId)
	}

	useEffect(() => {
		setTimeout(() => {
			mount()
		}, 5)

		const cb = () => {
			const maybeEditor = window.tinymce.editors[editorId]

			if (maybeEditor) {
				maybeEditor.setContent(option.value)
			}
		}

		ctEvents.on('ct:options:wp-editor:revert', cb)

		return () => {
			ctEvents.off('ct:options:wp-editor:revert', cb)

			setTimeout(() => {
				unmount()
			}, 300)
		}
	}, [])

	return (
		<div className="ct-option-editor" {...(option.attr || {})}>
			<textarea
				style={{ opacity: 0 }}
				id={editorId}
				ref={el}
				value={value}
				className="wp-editor-area"
				{...{
					...(option.field_attr ? option.field_attr : {}),
					...(option.attr && option.attr.placeholder
						? {
								placeholder: option.attr.placeholder,
						  }
						: {}),
				}}
				onChange={({ target: { value } }) => onChange(value)}
			/>
		</div>
	)
}

WpEditor.renderingConfig = {
	performRevert: ({ onChangeFor }) => {
		ctEvents.trigger('ct:options:wp-editor:revert')
	},
}

export default WpEditor
