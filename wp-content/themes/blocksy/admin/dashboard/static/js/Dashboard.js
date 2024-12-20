import { useRef, createElement, Component } from '@wordpress/element'
import DashboardContext, { Provider, getDefaultValue } from './context'
import Heading from './Heading'
import {
	Router,
	Link,
	Match,
	Location,
	LocationProvider,
	navigate,
	createHistory,
} from '@reach/router'
import ctEvents from 'ct-events'
import { Transition, animated } from 'react-spring'

window.ctDashboardLocalizations.DashboardContext = DashboardContext

import Navigation from './Navigation'
import Home from './screens/Home'
import RecommendedPlugins from './screens/RecommendedPlugins'
import Changelog from './screens/Changelog'
import windowHashSource from './window-hash-source'

let history = createHistory(windowHashSource())

let previousLocation = {
	pathname: location.hash.replace('#', '') || '/',
}

history.listen(({ location }) => {
	setTimeout(() => {
		previousLocation = location
	}, 10)
})

const SpringRouter = ({ children }) => {
	const transitionRef = useRef()

	return (
		<Location>
			{({ location, navigate, ...rest }) => {
				const nonAnimatedChildren = (location) => (
					<Router
						primary={false}
						location={location}
						navigate={navigate}>
						{children}
					</Router>
				)

				return (
					<Transition
						items={location}
						initial={null}
						keys={(location) => location.pathname}
						from={{ opacity: 0 }}
						enter={[{ opacity: 1 }]}
						leave={[{ opacity: 0 }]}
						config={(key, phase) => {
							const isImmediate =
								previousLocation &&
								previousLocation.pathname.indexOf(
									'extensions'
								) > -1 &&
								location.pathname.indexOf('extensions') > -1

							return phase === 'leave'
								? {
										duration: isImmediate ? 0 : 300,
								  }
								: {
										delay: isImmediate ? 0 : 300,
										duration: isImmediate ? 0 : 300,
								  }
						}}>
						{(props, location) => {
							return (
								<animated.div
									style={{
										...props,
									}}>
									{nonAnimatedChildren(location)}
								</animated.div>
							)
						}}
					</Transition>
				)
			}}
		</Location>
	)
}

const FadeTransitionRouter = (props) => (
	<Location>
		{({ location }) => (
			<TransitionGroup className="transition-group">
				<CSSTransition
					key={location.key}
					classNames="fade"
					timeout={500}>
					{/* the only difference between a router animation and
              any other animation is that you have to pass the
              location to the router so the old screen renders
              the "old location" */}
					<Router
						location={location}
						className="router"
						primary={false}>
						{props.children}
					</Router>
				</CSSTransition>
			</TransitionGroup>
		)}
	</Location>
)

export default class Dashboard extends Component {
	render() {
		const userRoutes = []
		ctEvents.trigger('ct:dashboard:routes', userRoutes)

		return (
			<LocationProvider history={history}>
				<Provider
					value={{
						...getDefaultValue(),
						...ctDashboardLocalizations,
						Link,
						Location,
						navigate,
						history,
						Match,
					}}>
					<header>
						<Heading />
						<Navigation />
					</header>

					<section>
						<SpringRouter primary={false} className="router">
							<Home path="/" />
							<RecommendedPlugins path="plugins" />
							<Changelog path="changelog" />

							{userRoutes.map(
								({ Component, key, path, ...props }) => (
									<Component
										key={key || path}
										path={path}
										{...props}
									/>
								)
							)}
						</SpringRouter>
					</section>
				</Provider>
			</LocationProvider>
		)
	}
}
