( () => {
	/**
	 * Initialize search form to convert query to uppercase before submission
	 */
	const initSearch = () => {
		const searchForm = document.querySelector( '.threew-header__search' );
		if ( ! searchForm ) {
			return;
		}

		const searchInput = searchForm.querySelector( '#threew-header-search' );
		if ( ! searchInput ) {
			return;
		}

		// Convert search query to uppercase on form submission
		searchForm.addEventListener( 'submit', () => {
			// Get the current value and trim whitespace
			const searchValue = searchInput.value.trim();

			// Convert to uppercase
			if ( searchValue ) {
				searchInput.value = searchValue.toUpperCase();
			}
		} );
	};

	// Initialize when DOM is ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initSearch );
	} else {
		initSearch();
	}
} )();
