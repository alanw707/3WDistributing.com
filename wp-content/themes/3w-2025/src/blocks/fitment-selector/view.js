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
import { __, sprintf } from '@wordpress/i18n';

const STEP_LABELS = [
	__( 'Vehicle year', 'threew-2025' ),
	__( 'Manufacturer', 'threew-2025' ),
	__( 'Model', 'threew-2025' ),
	__( 'Trim', 'threew-2025' ),
];

// translators: 1: current step number (1-4). 2: translated label for the current step.
const STEP_STATUS_TEMPLATE = __( 'Step %1$d of 4: %2$s', 'threew-2025' );
const STEP_STATUS_DEFAULT = __( 'Start with vehicle year', 'threew-2025' );

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
		this.resetButton = container.querySelector(
			'.threew-fitment-block__reset'
		);
		this.wrapper = container.closest( '.threew-fitment-block' );
		this.progressSteps = Array.from(
			this.wrapper?.querySelectorAll( '[data-fitment-step]' ) || []
		);
		this.progressStatus = this.wrapper?.querySelector(
			'[data-fitment-step-status]'
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
		this.updateSubmitButton();
		this.syncProgress();
	}

	/**
	 * Toggle loading state visuals on a select element.
	 *
	 * @param {HTMLSelectElement} select    Target select element.
	 * @param {boolean}           isLoading Whether loading indicator is active.
	 */
	setLoadingState( select, isLoading ) {
		if ( ! select ) {
			return;
		}

		if ( isLoading ) {
			select.classList.add( 'is-loading' );
			select.setAttribute( 'aria-busy', 'true' );
		} else {
			select.classList.remove( 'is-loading' );
			select.removeAttribute( 'aria-busy' );
		}
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
	 * Remove any stored vehicle selection from localStorage.
	 */
	clearVehicle() {
		try {
			const storage = this.getStorage();
			if ( ! storage ) {
				return;
			}

			storage.removeItem( 'threew_selected_vehicle' );
		} catch ( error ) {
			// Silently fail; clearing is a progressive enhancement.
		}
	}

	/**
	 * Populate year dropdown from API
	 */
	async populateYears() {
		this.setLoadingState( this.yearSelect, true );
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
		} finally {
			this.setLoadingState( this.yearSelect, false );
		}
	}

	/**
	 * Populate make dropdown based on selected year
	 *
	 * @param {string} year Selected vehicle year.
	 */
	async populateMakes( year ) {
		this.setLoadingState( this.makeSelect, true );
		try {
			const makes = await apiFetch( {
				path: `/threew/v1/fitment/makes?year=${ year }`,
			} );
			this.populateSelect( this.makeSelect, makes, 'Manufacturer' );
			this.makeSelect.disabled = false;
		} catch ( error ) {
			this.showError( 'Unable to load vehicle makes.' );
		} finally {
			this.setLoadingState( this.makeSelect, false );
		}
	}

	/**
	 * Populate model dropdown based on selected year and make
	 *
	 * @param {string} year Selected vehicle year.
	 * @param {string} make Selected vehicle make.
	 */
	async populateModels( year, make ) {
		this.setLoadingState( this.modelSelect, true );
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
		} finally {
			this.setLoadingState( this.modelSelect, false );
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
		this.setLoadingState( this.trimSelect, true );
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
		} finally {
			this.setLoadingState( this.trimSelect, false );
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

		// Insert a disabled placeholder that shows in the closed state.
		select.innerHTML = `<option value="" disabled selected>Select ${ placeholder }</option>`;
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

		// Restore disabled placeholder and reset value
		select.innerHTML = `<option value="" disabled selected>Select ${ placeholder }</option>`;
		select.disabled = true;
		select.value = '';
		this.setLoadingState( select, false );
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

		if ( this.progressStatus ) {
			const hasSelection = values.some( ( value ) => Boolean( value ) );
			const message = hasSelection
				? sprintf(
						STEP_STATUS_TEMPLATE,
						activeIndex + 1,
						STEP_LABELS[ activeIndex ]
				  )
				: STEP_STATUS_DEFAULT;
			this.progressStatus.textContent = message;
		}
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
		this.container?.addEventListener(
			'submit',
			this.handleSubmit.bind( this )
		);
		this.container?.addEventListener(
			'reset',
			this.handleReset.bind( this )
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
	 * Handle manual reset from the clear vehicle control.
	 *
	 * @param {Event} event Reset event from the form element.
	 */
	handleReset( event ) {
		event.preventDefault();

		this.state = {
			year: '',
			make: '',
			model: '',
			trim: '',
		};

		if ( this.yearSelect ) {
			this.yearSelect.value = '';
			this.yearSelect.disabled = false;
			this.setLoadingState( this.yearSelect, false );
		}

		this.resetSelect( this.makeSelect, 'Manufacturer' );
		this.resetSelect( this.modelSelect, 'Model' );
		this.resetSelect( this.trimSelect, 'Trim' );

		this.updateSubmitButton();
		this.clearVehicle();
		if ( typeof window !== 'undefined' ) {
			window.dispatchEvent(
				new CustomEvent( 'threew-vehicle-selected', {
					detail: { ...this.state },
				} )
			);
		}
		this.syncProgress();
		this.yearSelect?.focus();
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

		if ( this.resetButton ) {
			const hasAnySelection = Boolean(
				this.state.year ||
					this.state.make ||
					this.state.model ||
					this.state.trim
			);
			this.resetButton.disabled = ! hasAnySelection;
		}
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
