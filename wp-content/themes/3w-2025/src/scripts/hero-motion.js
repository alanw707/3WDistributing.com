const prefersReducedMotion = window.matchMedia(
	'(prefers-reduced-motion: reduce)'
).matches;

const supportItems = document.querySelectorAll( '[data-hero-support]' );

if (
	supportItems.length &&
	! prefersReducedMotion &&
	'IntersectionObserver' in window
) {
	const observer = new window.IntersectionObserver(
		( entries, obs ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					obs.unobserve( entry.target );
				}
			} );
		},
		{
			threshold: 0.35,
			rootMargin: '0px 0px -10%',
		}
	);

	supportItems.forEach( ( item ) => observer.observe( item ) );
} else if ( supportItems.length ) {
	supportItems.forEach( ( item ) => item.classList.add( 'is-visible' ) );
}

export {};
