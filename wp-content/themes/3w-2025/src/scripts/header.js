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

		const updateOverlayOffset = () => {
			if ( ! overlay ) {
				return;
			}

			if ( header.dataset.navOpen === 'true' ) {
				const { bottom } = header.getBoundingClientRect();
				const topOffset = Math.max( 0, Math.round( bottom ) );
				overlay.style.setProperty(
					'--threew-header-overlay-top',
					`${ topOffset }px`
				);
			} else {
				overlay.style.removeProperty( '--threew-header-overlay-top' );
			}
		};

		const updateDrawerMaxHeight = () => {
			if ( header.dataset.navOpen !== 'true' ) {
				drawer.style.removeProperty(
					'--threew-header-drawer-max-height'
				);
				return;
			}

			const viewport = window.visualViewport;
			const viewportHeight = viewport?.height ?? window.innerHeight ?? 0;
			const viewportOffsetTop = viewport?.offsetTop ?? 0;
			const { top } = drawer.getBoundingClientRect();
			const drawerTopWithinViewport = top - viewportOffsetTop;
			const breathingRoom = 24;
			const availableHeight = Math.max(
				0,
				Math.round(
					viewportHeight - drawerTopWithinViewport - breathingRoom
				)
			);

			if ( availableHeight > 0 ) {
				drawer.style.setProperty(
					'--threew-header-drawer-max-height',
					`${ availableHeight }px`
				);
				return;
			}

			drawer.style.removeProperty( '--threew-header-drawer-max-height' );
		};

		const syncNavMetrics = () => {
			updateOverlayOffset();
			updateDrawerMaxHeight();
		};

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
		const handleViewportMetricsChange = () => {
			syncCompactState();
			syncNavMetrics();
		};
		window.addEventListener( 'resize', handleViewportMetricsChange );
		window.addEventListener(
			'orientationchange',
			handleViewportMetricsChange
		);

		const setOpenState = ( isOpen, { focusToggle = false } = {} ) => {
			header.dataset.navOpen = isOpen ? 'true' : 'false';
			toggle.setAttribute( 'aria-expanded', String( isOpen ) );
			header.classList.toggle( 'threew-header--open', isOpen );
			if ( isOpen ) {
				const headerHeight = Math.round(
					header.getBoundingClientRect().height
				);
				header.style.setProperty(
					'--threew-header-open-offset',
					`${ headerHeight }px`
				);
				document.documentElement.classList.add( 'threew-body--nav-open' );
				document.body.classList.add( 'threew-body--nav-open' );
			} else {
				header.style.removeProperty( '--threew-header-open-offset' );
				document.documentElement.classList.remove( 'threew-body--nav-open' );
				document.body.classList.remove( 'threew-body--nav-open' );
			}
			if ( overlay ) {
				overlay.classList.toggle( 'is-active', isOpen );
				overlay.toggleAttribute( 'aria-hidden', ! isOpen );
			}

			syncNavMetrics();
			if ( isOpen ) {
				window.requestAnimationFrame( syncNavMetrics );
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

		const visualViewport = window.visualViewport;
		if ( visualViewport ) {
			visualViewport.addEventListener(
				'resize',
				handleViewportMetricsChange
			);
			visualViewport.addEventListener(
				'scroll',
				handleViewportMetricsChange
			);
		}

		syncCompactState();
		syncNavMetrics();
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initHeaderNav );
	} else {
		initHeaderNav();
	}
} )();
