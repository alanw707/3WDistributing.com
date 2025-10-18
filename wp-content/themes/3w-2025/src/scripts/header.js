( () => {
	const initHeaderNav = () => {
		const header = document.querySelector( '.threew-header' );
		if ( ! header ) {
			return;
		}

		const toggle = header.querySelector( '.threew-header__toggle' );
		const drawer = header.querySelector( '#threew-header-drawer' );

		if ( ! toggle || ! drawer ) {
			return;
		}

		const overlay = header.querySelector( '.threew-header__overlay' );
		const COMPACT_THRESHOLD = 96;
		let compactApplied = false;

		const syncCompactState = () => {
			if ( header.dataset.navOpen === 'true' ) {
				header.classList.remove( 'threew-header--compact' );
				compactApplied = false;
				return;
			}

			const shouldCompact = window.scrollY > COMPACT_THRESHOLD;
			if ( shouldCompact !== compactApplied ) {
				header.classList.toggle(
					'threew-header--compact',
					shouldCompact
				);
				compactApplied = shouldCompact;
			}
		};

		let scrollTicking = false;
		const handleScroll = () => {
			if ( scrollTicking ) {
				return;
			}

			scrollTicking = true;
			window.requestAnimationFrame( () => {
				syncCompactState();
				scrollTicking = false;
			} );
		};

		window.addEventListener( 'scroll', handleScroll, { passive: true } );
		window.addEventListener( 'resize', syncCompactState );
		window.addEventListener( 'orientationchange', syncCompactState );

		const setOpenState = ( isOpen, { focusToggle = false } = {} ) => {
			header.dataset.navOpen = isOpen ? 'true' : 'false';
			toggle.setAttribute( 'aria-expanded', String( isOpen ) );
			header.classList.toggle( 'threew-header--open', isOpen );
			document.body.classList.toggle( 'threew-body--nav-open', isOpen );
			if ( overlay ) {
				overlay.classList.toggle( 'is-active', isOpen );
				overlay.toggleAttribute( 'aria-hidden', ! isOpen );
			}

			if ( isOpen ) {
				header.classList.remove( 'threew-header--compact' );
				compactApplied = false;
			} else {
				syncCompactState();
			}

			if ( ! isOpen && focusToggle ) {
				toggle.focus();
			}
		};

		toggle.addEventListener( 'click', () => {
			const isOpen = header.dataset.navOpen === 'true';
			setOpenState( ! isOpen );
		} );

		const closeNav = ( options = {} ) => {
			if ( header.dataset.navOpen === 'true' ) {
				setOpenState( false, options );
			}
		};

		const handleKeydown = ( event ) => {
			if ( event.key === 'Escape' ) {
				closeNav( { focusToggle: true } );
			}
		};

		const handleClickOutside = ( event ) => {
			if ( header.dataset.navOpen !== 'true' ) {
				return;
			}

			if ( ! header.contains( event.target ) ) {
				closeNav();
			}
		};

		const collapseOnLinkClick = ( event ) => {
			if (
				event.target instanceof window.HTMLElement &&
				event.target.tagName === 'A'
			) {
				closeNav();
			}
		};

		drawer.addEventListener( 'click', collapseOnLinkClick );
		if ( overlay ) {
			overlay.addEventListener( 'click', () => closeNav() );
		}
		document.addEventListener( 'keydown', handleKeydown );
		document.addEventListener( 'click', handleClickOutside );

		const mql = window.matchMedia( '(min-width: 64rem)' );
		const handleViewportChange = ( evt ) => {
			if ( evt.matches ) {
				closeNav();
			}
		};

		if ( typeof mql.addEventListener === 'function' ) {
			mql.addEventListener( 'change', handleViewportChange );
		} else if ( typeof mql.addListener === 'function' ) {
			mql.addListener( handleViewportChange );
		}

		syncCompactState();
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initHeaderNav );
	} else {
		initHeaderNav();
	}
} )();
