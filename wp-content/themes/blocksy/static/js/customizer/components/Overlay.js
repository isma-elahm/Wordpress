import {
	createElement,
	Component,
	useEffect,
	useState,
	useContext,
	createContext,
	Fragment,
} from '@wordpress/element'
import { Dialog, DialogOverlay, DialogContent } from './reach/dialog'
import { Transition, animated } from 'react-spring'
import { __ } from 'ct-i18n'
import classnames from 'classnames'

const AnimatedDialogOverlay = animated(DialogOverlay)
const AnimatedDialogContent = animated(DialogContent)

const defaultIsVisible = (i) => !!i

const Overlay = ({
	items,
	isVisible = defaultIsVisible,
	render,
	className,
	initialFocusRef,
	onDismiss,
	onCloseButtonClick,
}) => {
	return (
		<Transition
			items={items}
			onStart={() =>
				document.body.classList[isVisible(items) ? 'add' : 'remove'](
					'ct-dashboard-overlay-open'
				)
			}
			config={{ duration: 200 }}
			from={{ opacity: 0, y: -10 }}
			enter={{ opacity: 1, y: 0 }}
			leave={{ opacity: 0, y: 10 }}>
			{(props, items) => {
				return (
					isVisible(items) && (
						<AnimatedDialogOverlay
							style={{ opacity: props.opacity }}
							container={document.body}
							onDismiss={() => onDismiss()}
							initialFocusRef={initialFocusRef}>
							<AnimatedDialogContent
								className={className}
								style={{
									transform: props.y.to(
										(y) => `translate3d(0px, ${y}px, 0px)`
									),
								}}>
								<button
									className="close-button"
									onClick={() =>
										onCloseButtonClick
											? onCloseButtonClick()
											: onDismiss()
									}>
									×
								</button>

								{render(items, props)}
							</AnimatedDialogContent>
						</AnimatedDialogOverlay>
					)
				)
			}}
		</Transition>
	)
}

export default Overlay
