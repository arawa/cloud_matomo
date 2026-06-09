import { getCurrentUser } from '@nextcloud/auth'

interface Options {
	url: string
	siteId: string
	trackUser?: boolean
	trackDir?: boolean
}

// use this for debugging
const matomoLog = false

window._paq = window._paq || [];

(function() {

	const trackPageView = () => {
		window._paq.push(['setDocumentTitle', document.title])
		window._paq.push(['setCustomUrl', location.href])
		window._paq.push(['trackPageView'])
		window._paq.push(['enableLinkTracking'])

		if (matomoLog) {
			console.log('Matomo tracking: track page view (cb) for url ' + location.href)
		}
	}

	const isFilesRoute = (url) => {
		return url.includes('/apps/files')
	}

	const onChange = () => {
		const url = location.href

		if (url === lastUrl) return
		lastUrl = url

		// filtrer uniquement Files
		if (!isFilesRoute(url)) return

		trackPageView()
	}

	let options: Options | null = null

	try {
		options = JSON.parse('%OPTIONS%')
	} catch (err) {}

	if (!options || !options.url || !options.siteId) {
		return
	}

	if (options.url[options.url.length - 1] !== '/') {
		options.url += '/'
	}

	let lastUrl: string = location.href
	let app: string | null = null
	const path: string = window.location.pathname
	const pathParts: RegExpMatchArray = path.match(/(?:index\.php\/)?apps\/([a-z0-9]+)\/?/i) || path.match(/(?:index\.php\/)?([a-z0-9]+)(\/([a-z0-9]+))?/i) || []

	if (pathParts.length >= 2) {
		app = pathParts[1]

		if (app === 's') {
			// rewrite app name
			app = 'share'

			let shareValue = document.querySelector('input[name="filename"]')?.value

			if (shareValue) {
				shareValue = pathParts[3] + ' (' + shareValue + ')'

				// save share id + share name in slot 3
				window._paq.push(['setCustomVariable', '3', 'ShareNodes', shareValue, 'page'])
			} else {
				shareValue = pathParts[3]
			}

			// save share id in slot 2
			window._paq.push(['setCustomVariable', '2', 'Shares', pathParts[3], 'page'])
		}

		// save app name in slot 1
		window._paq.push(['setCustomVariable', '1', 'App', app, 'page']) // old way, to be removed in favor of setCustomDimension
		window._paq.push(['setCustomDimension', 1, app])
	}

	// track user id if enabled by configuration
	if (options.trackUser) {
		const user = getCurrentUser()
		// set user id
		if (user) {
			window._paq.push(['setUserId', user.uid])
		}
	}

	// track directory browsing in files app if enabled
	if (options.trackDir) {
		// track file browsing
		const wrap = (method: 'pushState' | 'replaceState') => {
			const original = history[method]

			history[method] = function(...args: any[]) {
				const result = original.apply(this, args)
				queueMicrotask(onChange)
				return result
			}
		}

		wrap('pushState')
		wrap('replaceState')

		window.addEventListener('popstate', onChange)
	}

	// set Matomo options
	window._paq.push(['setTrackerUrl', options.url + 'matomo.php'])
	window._paq.push(['setSiteId', options.siteId])

	if (app !== 'files' || !options.trackDir) {
		// track page view
		window._paq.push(['trackPageView'])
		window._paq.push(['enableLinkTracking'])
		if (matomoLog) {
			console.log('Matomo tracking: track page view for app ' + app)
		}
	}

	if (typeof Matomo === 'undefined') {
		// load Matomo library
		const d = document
		const g = d.createElement('script')
		const s = d.getElementsByTagName('script')[0]
		g.type = 'text/javascript'
		g.async = true
		g.defer = true
		g.src = options.url + 'matomo.js'
		s.parentNode.insertBefore(g, s)
	}
}())
