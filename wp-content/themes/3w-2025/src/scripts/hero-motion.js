const revealSupportItems = () => {
	const supportItems = document.querySelectorAll( '[data-hero-support]' );
	if ( ! supportItems.length ) {
		return;
	}

	supportItems.forEach( ( item ) => item.classList.add( 'is-visible' ) );
};

const initHeroSupport = () => {
	if ( typeof document === 'undefined' ) {
		return;
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', revealSupportItems, {
			once: true,
		} );
		return;
	}

	revealSupportItems();
};

initHeroSupport();

export {};
