( () => {
	const initScrollToTop = () => {
		const existingButton = document.querySelector( '.threew-scroll-top' );
		if ( existingButton ) {
			return;
		}

		const button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'threew-scroll-top';
		button.setAttribute( 'aria-label', 'Scroll back to top' );

		const icon = document.createElementNS(
			'http://www.w3.org/2000/svg',
			'svg'
		);
		icon.setAttribute( 'viewBox', '0 0 24 24' );
		icon.setAttribute( 'aria-hidden', 'true' );
		icon.setAttribute( 'focusable', 'false' );
		icon.classList.add( 'threew-scroll-top__icon' );

		const arrowPath = document.createElementNS(
			'http://www.w3.org/2000/svg',
			'path'
		);
		arrowPath.setAttribute(
			'd',
			'M12 5.25l6.25 6.25-1.5 1.5L12 8.5l-4.75 4.5-1.5-1.5z'
		);
		arrowPath.setAttribute( 'fill', 'currentColor' );
		icon.appendChild( arrowPath );

		const label = document.createElement( 'span' );
		label.className = 'threew-scroll-top__label';
		label.textContent = 'Top';

		button.append( icon, label );
		document.body.appendChild( button );

		const SCROLL_THRESHOLD = 320;
		let isVisible = false;
		let isTicking = false;

		const updateVisibility = () => {
			const shouldShow = window.scrollY > SCROLL_THRESHOLD;
			if ( shouldShow !== isVisible ) {
				button.classList.toggle( 'is-visible', shouldShow );
				isVisible = shouldShow;
			}
			isTicking = false;
		};

		const requestUpdate = () => {
			if ( isTicking ) {
				return;
			}
			isTicking = true;
			window.requestAnimationFrame( updateVisibility );
		};

		window.addEventListener( 'scroll', requestUpdate, { passive: true } );
		window.addEventListener( 'resize', requestUpdate );
		window.addEventListener( 'orientationchange', requestUpdate );

		button.addEventListener( 'click', () => {
			window.scrollTo( { top: 0, behavior: 'smooth' } );
			button.classList.add( 'is-triggered' );
			window.setTimeout( () => {
				button.classList.remove( 'is-triggered' );
			}, 700 );
		} );

		updateVisibility();
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initScrollToTop );
	} else {
		initScrollToTop();
	}
} )();
