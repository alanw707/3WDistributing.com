/**
 * Smooth scroll to anchor links
 */
( () => {
	const initSmoothScroll = () => {
		// Handle clicks on anchor links
		document.addEventListener( 'click', ( event ) => {
			const link = event.target.closest( 'a[href*="#"]' );
			if ( ! link ) {
				return;
			}

			const href = link.getAttribute( 'href' );
			if ( ! href ) {
				return;
			}

			// Parse the URL to get the hash
			const url = new URL( href, window.location.origin );
			const hash = url.hash;

			// Only handle if there's a hash
			if ( ! hash || hash === '#' ) {
				return;
			}

			// Check if this is a same-page link or cross-page link to home
			const isSamePage = url.pathname === window.location.pathname;
			const isHomeLink =
				url.pathname === '/' && window.location.pathname !== '/';

			if ( isSamePage ) {
				// Same page - smooth scroll
				event.preventDefault();
				const targetId = hash.substring( 1 );
				const targetElement = document.getElementById( targetId );

				if ( targetElement ) {
					const headerOffset = 80; // Adjust based on your fixed header height
					const elementPosition =
						targetElement.getBoundingClientRect().top;
					const offsetPosition =
						elementPosition + window.scrollY - headerOffset;

					window.scrollTo( {
						top: offsetPosition,
						behavior: 'smooth',
					} );

					// Update URL without triggering navigation
					if ( window.history.pushState ) {
						window.history.pushState( null, '', hash );
					}

					// Set focus for accessibility
					targetElement.setAttribute( 'tabindex', '-1' );
					targetElement.focus( { preventScroll: true } );
				}
			}
			// For cross-page links (like clicking Vendors from another page),
			// let the browser navigate normally - the hash will be handled on page load
		} );

		// Handle hash on page load (for direct links or cross-page navigation)
		const handleHashOnLoad = () => {
			const hash = window.location.hash;
			if ( ! hash || hash === '#' ) {
				return;
			}

			const targetId = hash.substring( 1 );
			const targetElement = document.getElementById( targetId );

			if ( targetElement ) {
				// Small delay to ensure page is fully loaded
				setTimeout( () => {
					const headerOffset = 80;
					const elementPosition =
						targetElement.getBoundingClientRect().top;
					const offsetPosition =
						elementPosition + window.scrollY - headerOffset;

					window.scrollTo( {
						top: offsetPosition,
						behavior: 'smooth',
					} );

					targetElement.setAttribute( 'tabindex', '-1' );
					targetElement.focus( { preventScroll: true } );
				}, 100 );
			}
		};

		// Run on page load
		handleHashOnLoad();
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initSmoothScroll );
	} else {
		initSmoothScroll();
	}
} )();
