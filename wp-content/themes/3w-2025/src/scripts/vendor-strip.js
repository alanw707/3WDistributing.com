// Hover/touch marquee control for vendor strip logos.
( () => {
	if ( typeof document === 'undefined' ) {
		return;
	}

	const marquees = document.querySelectorAll(
		'.threew-vendor-strip__marquee'
	);

	if ( ! marquees.length ) {
		return;
	}

	const supportsMatchMedia =
		typeof window !== 'undefined' &&
		typeof window.matchMedia === 'function';

	const prefersReducedMotion = supportsMatchMedia
		? window.matchMedia( '(prefers-reduced-motion: reduce)' )
		: null;

	if ( prefersReducedMotion?.matches ) {
		// Respect user preference: leave marquee static.
		return;
	}

	const hoverNoneQuery = supportsMatchMedia
		? window.matchMedia( '(hover: none)' )
		: null;

	const setAutoplay = ( shouldAutoplay ) => {
		marquees.forEach( ( marquee ) => {
			marquee.classList.toggle( 'is-autoplay', shouldAutoplay );
		} );
	};

	if ( hoverNoneQuery ) {
		setAutoplay( hoverNoneQuery.matches );
		hoverNoneQuery.addEventListener( 'change', ( event ) => {
			setAutoplay( event.matches );
		} );
	}

	marquees.forEach( ( marquee ) => {
		const activate = () => marquee.classList.add( 'is-active' );
		const deactivate = () => marquee.classList.remove( 'is-active' );

		marquee.addEventListener( 'mouseenter', activate );
		marquee.addEventListener( 'mouseleave', deactivate );
		marquee.addEventListener( 'focusin', activate );
		marquee.addEventListener( 'focusout', deactivate );

		// For touch devices without hover, allow tap to pause/play by toggling.
		marquee.addEventListener( 'pointerdown', () => {
			if ( hoverNoneQuery?.matches ) {
				marquee.classList.toggle( 'is-autoplay' );
			}
		} );
	} );
} )();
