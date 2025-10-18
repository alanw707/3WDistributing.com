/**
 * Fitment Selector - Frontend Interactive Component
 *
 * Handles dynamic Year → Make → Model → Trim selection with:
 * - REST API integration for fitment data
 * - localStorage persistence across pages
 * - Cascading dropdown behavior
 * - WooCommerce product filtering integration
 */

import apiFetch from '@wordpress/api-fetch';

class FitmentSelector {
	constructor( container ) {
		this.container = container;
		this.yearSelect = container.querySelector( '#threew-fitment-year' );
		this.makeSelect = container.querySelector( '#threew-fitment-make' );
		this.modelSelect = container.querySelector( '#threew-fitment-model' );
		this.trimSelect = container.querySelector( '#threew-fitment-trim' );
		this.submitButton = container.querySelector(
			'.threew-fitment-block__submit'
		);
		this.wrapper = container.closest( '.threew-fitment-block' );
		this.progressSteps = Array.from(
			this.wrapper?.querySelectorAll( '[data-fitment-step]' ) || []
		);

		this.state = {
			year: '',
			make: '',
			model: '',
			trim: '',
		};

		this.init();
	}

	/**
	 * Safely return localStorage interface when available.
	 *
	 * @return {Storage|null} Browser storage or null when unavailable.
	 */
	getStorage() {
		try {
			if (
				typeof window !== 'undefined' &&
				'localStorage' in window &&
				window.localStorage
			) {
				return window.localStorage;
			}
		} catch ( error ) {
			// Swallow quota/security errors.
		}

		return null;
	}

	/**
	 * Initialize selector with saved state and event listeners
	 */
	async init() {
		// Load saved vehicle from localStorage
		this.loadSavedVehicle();

		// Populate year dropdown
		await this.populateYears();

		// Attach event listeners
		this.attachEventListeners();

		// Restore saved selections
		if ( this.state.year ) {
			await this.handleYearChange( {
				target: { value: this.state.year },
			} );
			if ( this.state.make ) {
				await this.handleMakeChange( {
					target: { value: this.state.make },
				} );
				if ( this.state.model ) {
					await this.handleModelChange( {
						target: { value: this.state.model },
					} );
					if ( this.state.trim ) {
						this.trimSelect.value = this.state.trim;
					}
				}
			}
		}

		// Mark as interactive (remove disabled state from save.js)
		this.container.setAttribute( 'data-fitment-interactive', 'ready' );
		this.syncProgress();
	}

	/**
	 * Load saved vehicle selection from localStorage
	 */
	loadSavedVehicle() {
		try {
			const storage = this.getStorage();
			if ( ! storage ) {
				return;
			}

			const saved = storage.getItem( 'threew_selected_vehicle' );
			if ( saved ) {
				this.state = JSON.parse( saved );
			}
		} catch ( error ) {
			this.showError( 'Unable to load saved vehicle preferences.' );
		}
	}

	/**
	 * Save current vehicle selection to localStorage
	 */
	saveVehicle() {
		try {
			const storage = this.getStorage();
			if ( ! storage ) {
				return;
			}

			storage.setItem(
				'threew_selected_vehicle',
				JSON.stringify( this.state )
			);

			// Dispatch custom event for other components to listen to
			window.dispatchEvent(
				new CustomEvent( 'threew-vehicle-selected', {
					detail: this.state,
				} )
			);
		} catch ( error ) {
			this.showError( 'Unable to persist your vehicle selection.' );
		}
	}

	/**
	 * Populate year dropdown from API
	 */
	async populateYears() {
		try {
			const years = await apiFetch( {
				path: '/threew/v1/fitment/years',
			} );
			this.populateSelect( this.yearSelect, years, 'Year' );
			this.yearSelect.disabled = false;
		} catch ( error ) {
			this.showError(
				'Unable to load vehicle years. Please refresh the page.'
			);
		}
	}

	/**
	 * Populate make dropdown based on selected year
	 *
	 * @param {string} year Selected vehicle year.
	 */
	async populateMakes( year ) {
		try {
			const makes = await apiFetch( {
				path: `/threew/v1/fitment/makes?year=${ year }`,
			} );
			this.populateSelect( this.makeSelect, makes, 'Manufacturer' );
			this.makeSelect.disabled = false;
		} catch ( error ) {
			this.showError( 'Unable to load vehicle makes.' );
		}
	}

	/**
	 * Populate model dropdown based on selected year and make
	 *
	 * @param {string} year Selected vehicle year.
	 * @param {string} make Selected vehicle make.
	 */
	async populateModels( year, make ) {
		try {
			const models = await apiFetch( {
				path: `/threew/v1/fitment/models?year=${ year }&make=${ encodeURIComponent(
					make
				) }`,
			} );
			this.populateSelect( this.modelSelect, models, 'Model' );
			this.modelSelect.disabled = false;
		} catch ( error ) {
			this.showError( 'Unable to load vehicle models.' );
		}
	}

	/**
	 * Populate trim dropdown based on selected year, make, and model
	 *
	 * @param {string} year  Selected vehicle year.
	 * @param {string} make  Selected vehicle make.
	 * @param {string} model Selected vehicle model.
	 */
	async populateTrims( year, make, model ) {
		try {
			const trims = await apiFetch( {
				path: `/threew/v1/fitment/trims?year=${ year }&make=${ encodeURIComponent(
					make
				) }&model=${ encodeURIComponent( model ) }`,
			} );
			this.populateSelect( this.trimSelect, trims, 'Trim' );
			this.trimSelect.disabled = false;
		} catch ( error ) {
			this.showError( 'Unable to load vehicle trims.' );
		}
	}

	/**
	 * Populate a select element with options
	 *
	 * @param {HTMLSelectElement} select      Target select element.
	 * @param {string[]}          options     Options to inject.
	 * @param {string}            placeholder Placeholder label.
	 */
	populateSelect( select, options, placeholder ) {
		if ( ! select ) {
			return;
		}

		select.innerHTML = `<option value="">Select ${ placeholder }</option>`;
		options.forEach( ( option ) => {
			const optionEl = document.createElement( 'option' );
			optionEl.value = option;
			optionEl.textContent = option;
			select.appendChild( optionEl );
		} );
	}

	/**
	 * Reset dependent dropdowns
	 *
	 * @param {HTMLSelectElement} select      Target select element.
	 * @param {string}            placeholder Placeholder label.
	 */
	resetSelect( select, placeholder ) {
		if ( ! select ) {
			return;
		}

		select.innerHTML = `<option value="">Select ${ placeholder }</option>`;
		select.disabled = true;
		select.value = '';
	}

	/**
	 * Update progress indicator to reflect current step state
	 */
	syncProgress() {
		if ( ! this.progressSteps.length ) {
			return;
		}

		const values = [
			this.state.year,
			this.state.make,
			this.state.model,
			this.state.trim,
		];
		let activeIndex = 0;

		if ( ! values[ 0 ] ) {
			activeIndex = 0;
		} else if ( ! values[ 1 ] ) {
			activeIndex = 1;
		} else if ( ! values[ 2 ] ) {
			activeIndex = 2;
		} else if ( ! values[ 3 ] ) {
			activeIndex = 3;
		} else {
			activeIndex = values.length - 1;
		}

		this.progressSteps.forEach( ( stepEl, index ) => {
			const hasValue = Boolean( values[ index ] );
			const isRequired = index < 3;
			stepEl.classList.toggle(
				'is-complete',
				hasValue && ( isRequired || hasValue )
			);
			const isActive = index === activeIndex;
			stepEl.classList.toggle( 'is-active', isActive );

			if ( isActive ) {
				stepEl.setAttribute( 'aria-current', 'step' );
			} else {
				stepEl.removeAttribute( 'aria-current' );
			}
		} );
	}

	/**
	 * Attach event listeners to all dropdowns and submit button
	 */
	attachEventListeners() {
		this.yearSelect?.addEventListener(
			'change',
			this.handleYearChange.bind( this )
		);
		this.makeSelect?.addEventListener(
			'change',
			this.handleMakeChange.bind( this )
		);
		this.modelSelect?.addEventListener(
			'change',
			this.handleModelChange.bind( this )
		);
		this.trimSelect?.addEventListener(
			'change',
			this.handleTrimChange.bind( this )
		);
		this.submitButton?.addEventListener(
			'click',
			this.handleSubmit.bind( this )
		);
	}

	/**
	 * Handle year selection change
	 *
	 * @param {Event} event Change event from the year dropdown.
	 */
	async handleYearChange( event ) {
		const year = event.target.value;
		this.state.year = year;
		this.state.make = '';
		this.state.model = '';
		this.state.trim = '';

		// Reset dependent dropdowns
		this.resetSelect( this.makeSelect, 'Manufacturer' );
		this.resetSelect( this.modelSelect, 'Model' );
		this.resetSelect( this.trimSelect, 'Trim' );

		if ( year ) {
			await this.populateMakes( year );
		}

		this.updateSubmitButton();
		this.saveVehicle();
		this.syncProgress();
	}

	/**
	 * Handle make selection change
	 *
	 * @param {Event} event Change event from the make dropdown.
	 */
	async handleMakeChange( event ) {
		const make = event.target.value;
		this.state.make = make;
		this.state.model = '';
		this.state.trim = '';

		// Reset dependent dropdowns
		this.resetSelect( this.modelSelect, 'Model' );
		this.resetSelect( this.trimSelect, 'Trim' );

		if ( make && this.state.year ) {
			await this.populateModels( this.state.year, make );
		}

		this.updateSubmitButton();
		this.saveVehicle();
		this.syncProgress();
	}

	/**
	 * Handle model selection change
	 *
	 * @param {Event} event Change event from the model dropdown.
	 */
	async handleModelChange( event ) {
		const model = event.target.value;
		this.state.model = model;
		this.state.trim = '';

		// Reset trim dropdown
		this.resetSelect( this.trimSelect, 'Trim' );

		if ( model && this.state.year && this.state.make ) {
			await this.populateTrims( this.state.year, this.state.make, model );
		}

		this.updateSubmitButton();
		this.saveVehicle();
		this.syncProgress();
	}

	/**
	 * Handle trim selection change
	 *
	 * @param {Event} event Change event from the trim dropdown.
	 */
	handleTrimChange( event ) {
		this.state.trim = event.target.value;
		this.updateSubmitButton();
		this.saveVehicle();
		this.syncProgress();
	}

	/**
	 * Update submit button state based on required selections
	 */
	updateSubmitButton() {
		if ( ! this.submitButton ) {
			return;
		}

		// Require at least Year, Make, Model (trim is optional)
		const isValid = this.state.year && this.state.make && this.state.model;
		this.submitButton.disabled = ! isValid;
	}

	/**
	 * Handle form submission
	 *
	 * @param {Event} event Form submission event.
	 */
	handleSubmit( event ) {
		event.preventDefault();

		const { year, make, model, trim } = this.state;

		if ( ! year || ! make || ! model ) {
			this.showError( 'Please select at least Year, Make, and Model.' );
			return;
		}

		// Build shop URL with fitment parameters
		const params = new URLSearchParams( {
			vehicle_year: year,
			vehicle_make: make,
			vehicle_model: model,
		} );

		if ( trim ) {
			params.append( 'vehicle_trim', trim );
		}

		// Navigate to shop with fitment filters
		window.location.href = `/shop?${ params.toString() }`;
	}

	/**
	 * Display error message to user
	 *
	 * @param {string} message Error message to display.
	 */
	showError( message ) {
		// Create or update error message element
		let errorEl = this.container.querySelector(
			'.threew-fitment-block__error'
		);

		if ( ! errorEl ) {
			errorEl = document.createElement( 'div' );
			errorEl.className = 'threew-fitment-block__error';
			errorEl.setAttribute( 'role', 'alert' );
			this.container.insertBefore( errorEl, this.container.firstChild );
		}

		errorEl.textContent = message;
		errorEl.style.display = 'block';

		// Auto-hide after 5 seconds
		setTimeout( () => {
			errorEl.style.display = 'none';
		}, 5000 );
	}
}

/**
 * Initialize all fitment selectors on the page
 */
document.addEventListener( 'DOMContentLoaded', () => {
	const selectors = document.querySelectorAll(
		'[data-fitment-interactive="pending"]'
	);
	selectors.forEach( ( container ) => {
		new FitmentSelector( container );
	} );
} );

// Export for potential use in other modules
export default FitmentSelector;
