// Lightweight enhancement: keep aria-expanded in sync for the SEO accordion
// while relying on native <details> behavior for toggling.

( () => {
	if ( typeof document === 'undefined' ) {
		return;
	}

	let generatedId = 0;

	const init = () => {
		const accordions = document.querySelectorAll(
			'[data-threew-seo-accordion]'
		);

		accordions.forEach( ( details ) => {
			const summary = details.querySelector( 'summary' );
			const content = details.querySelector(
				'.threew-seo-accordion__content'
			);

			if ( ! summary || ! content ) {
				return;
			}

			if ( ! content.id ) {
				generatedId += 1;
				content.id = `threew-seo-content-${ generatedId }`;
			}

			summary.setAttribute( 'aria-controls', content.id );

			const syncExpanded = () => {
				summary.setAttribute(
					'aria-expanded',
					details.open ? 'true' : 'false'
				);
			};

			syncExpanded();
			details.addEventListener( 'toggle', syncExpanded );
		} );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init, { once: true } );
	} else {
		init();
	}
} )();

export {};
