// Progressive enhancement for <details>-based SEO intro accordion
// Adds smooth height/opacity/translate transitions on open/close and
// syncs aria-expanded for assistive tech.

( () => {
	if ( typeof document === 'undefined' ) {
		return;
	}

	const reduceMotion =
		typeof window !== 'undefined' &&
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	const DURATION = reduceMotion ? 0 : 320; // ms
	const EASE = 'cubic-bezier(.3,.7,.2,1)';
	const scheduleFrame =
		typeof window !== 'undefined' && window.requestAnimationFrame
			? window.requestAnimationFrame.bind( window )
			: ( callback ) => window.setTimeout( callback, 16 );

	const init = () => {
		const accordions = document.querySelectorAll(
			'[data-threew-seo-accordion]'
		);
		accordions.forEach( ( details ) => enhanceDetails( details ) );
	};

	const enhanceDetails = ( details ) => {
		const DetailsElement =
			typeof window !== 'undefined'
				? window.HTMLDetailsElement
				: undefined;

		if ( ! DetailsElement || ! ( details instanceof DetailsElement ) ) {
			return;
		}
		const summary = details.querySelector( 'summary' );
		const content = details.querySelector(
			'.threew-seo-accordion__content'
		);
		if ( ! summary || ! content ) {
			return;
		}

		// a11y: tie summary to content and manage expanded state
		const contentId = content.id || 'threew-seo-content';
		content.id = contentId;
		summary.setAttribute( 'aria-controls', contentId );
		summary.setAttribute(
			'aria-expanded',
			details.open ? 'true' : 'false'
		);

		let animating = false;
		let animTimer = null;

		const cleanup = () => {
			content.style.removeProperty( 'max-height' );
			content.style.removeProperty( 'overflow' );
			content.style.removeProperty( 'transition' );
			content.style.removeProperty( 'opacity' );
			content.style.removeProperty( 'transform' );
			details.classList.remove( 'is-opening', 'is-closing' );
		};

		const hostSection = details.closest( '.threew-seo-intro' );

		const animateOpen = () => {
			animating = true;
			details.classList.add( 'is-opening' );
			hostSection?.classList.add( 'is-animating' );

			// Start from collapsed
			content.style.overflow = 'hidden';
			content.style.maxHeight = '0px';
			content.style.opacity = '0';
			content.style.transform = 'translateY(-6px)';

			scheduleFrame( () => {
				const h = content.scrollHeight;
				content.style.transition = `max-height ${ DURATION }ms ${ EASE }, opacity ${ Math.max(
					160,
					DURATION - 80
				) }ms ease, transform ${ DURATION }ms ${ EASE }`;
				content.style.maxHeight = `${ h }px`;
				content.style.opacity = '1';
				content.style.transform = 'translateY(0)';
			} );

			animTimer = window.setTimeout( () => {
				cleanup();
				summary.setAttribute( 'aria-expanded', 'true' );
				animating = false;
				animTimer = null;
				hostSection?.classList.remove( 'is-animating' );
			}, DURATION + 40 );
		};

		const animateClose = () => {
			animating = true;
			details.classList.add( 'is-closing' );
			hostSection?.classList.add( 'is-animating' );

			const h = content.scrollHeight;
			content.style.overflow = 'hidden';
			content.style.maxHeight = `${ h }px`;
			content.style.opacity = '1';
			content.style.transform = 'translateY(0)';

			// Force reflow to apply starting height
			// eslint-disable-next-line no-unused-expressions
			content.getBoundingClientRect();

			content.style.transition = `max-height ${ DURATION }ms ${ EASE }, opacity ${ Math.max(
				160,
				DURATION - 80
			) }ms ease, transform ${ DURATION }ms ${ EASE }`;
			content.style.maxHeight = '0px';
			content.style.opacity = '0';
			content.style.transform = 'translateY(-6px)';

			animTimer = window.setTimeout( () => {
				details.removeAttribute( 'open' );
				cleanup();
				summary.setAttribute( 'aria-expanded', 'false' );
				animating = false;
				animTimer = null;
				hostSection?.classList.remove( 'is-animating' );
			}, DURATION + 40 );
		};

		const handleToggle = () => {
			if ( animating ) {
				// Allow immediate reversal: if opening, switch to closing; if closing, open.
				if ( details.classList.contains( 'is-opening' ) ) {
					if ( animTimer ) {
						window.clearTimeout( animTimer );
					}
					animateClose();
				} else if ( details.classList.contains( 'is-closing' ) ) {
					if ( animTimer ) {
						window.clearTimeout( animTimer );
					}
					details.setAttribute( 'open', '' );
					animateOpen();
				}
				return;
			}
			if ( details.open ) {
				animateClose();
			} else {
				details.setAttribute( 'open', '' );
				animateOpen();
			}
		};

		summary.addEventListener( 'click', ( ev ) => {
			ev.preventDefault();
			handleToggle();
		} );

		summary.addEventListener( 'keydown', ( ev ) => {
			const key = ev.key || ev.code;
			if ( key === 'Enter' || key === ' ' || key === 'Spacebar' ) {
				ev.preventDefault();
				handleToggle();
			}
		} );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
