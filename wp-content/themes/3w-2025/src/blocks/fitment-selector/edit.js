import { __, sprintf } from '@wordpress/i18n';
import { useMemo, useState } from '@wordpress/element';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

const INVENTORY = {
	2025: {
		Audi: {
			RS7: [ 'Performance', 'Prestige' ],
			Q8: [ 'S-Line', 'Base' ],
		},
		BMW: {
			M4: [ 'Competition', 'CS' ],
			X5: [ 'M Sport', 'Luxury' ],
		},
	},
	2024: {
		Mercedes: {
			C63: [ 'S', 'Edition 1' ],
			G63: [ 'Standard' ],
		},
		Porsche: {
			911: [ 'Carrera S', 'Turbo' ],
			Taycan: [ 'Turbo', 'GTS' ],
		},
	},
	2023: {
		Lexus: {
			IS500: [ 'F SPORT Performance' ],
		},
		Nissan: {
			'GT-R': [ 'Premium', 'NISMO' ],
		},
	},
};

const getYears = () => Object.keys( INVENTORY );

const getMakes = ( year ) =>
	year && INVENTORY[ year ] ? Object.keys( INVENTORY[ year ] ) : [];

const getModels = ( year, make ) =>
	year && make && INVENTORY[ year ] && INVENTORY[ year ][ make ]
		? Object.keys( INVENTORY[ year ][ make ] )
		: [];

const getTrims = ( year, make, model ) =>
	year && make && model && INVENTORY[ year ] && INVENTORY[ year ][ make ]
		? INVENTORY[ year ][ make ][ model ] || []
		: [];

const STEP_IDS = [ 'year', 'make', 'model', 'trim' ];

export default function Edit( { attributes, setAttributes } ) {
	const { headline, subheadline, ctaLabel } = attributes;
	const blockProps = useBlockProps( { className: 'threew-fitment-block' } );

	const [ year, setYear ] = useState( '' );
	const [ make, setMake ] = useState( '' );
	const [ model, setModel ] = useState( '' );
	const [ trim, setTrim ] = useState( '' );

	const makes = useMemo( () => getMakes( year ), [ year ] );
	const models = useMemo( () => getModels( year, make ), [ year, make ] );
	const trims = useMemo(
		() => getTrims( year, make, model ),
		[ year, make, model ]
	);

	const handleYearChange = ( event ) => {
		setYear( event.target.value );
		setMake( '' );
		setModel( '' );
		setTrim( '' );
	};

	const handleMakeChange = ( event ) => {
		setMake( event.target.value );
		setModel( '' );
		setTrim( '' );
	};

	const handleModelChange = ( event ) => {
		setModel( event.target.value );
		setTrim( '' );
	};

	const handleReset = () => {
		setYear( '' );
		setMake( '' );
		setModel( '' );
		setTrim( '' );
	};

	const stepValues = [ year, make, model, trim ];

	let activeIndex = 0;
	if ( ! stepValues[ 0 ] ) {
		activeIndex = 0;
	} else if ( ! stepValues[ 1 ] ) {
		activeIndex = 1;
	} else if ( ! stepValues[ 2 ] ) {
		activeIndex = 2;
	} else if ( ! stepValues[ 3 ] ) {
		activeIndex = 3;
	} else {
		activeIndex = stepValues.length - 1;
	}

	const stepLabels = [
		__( 'Vehicle year', 'threew-2025' ),
		__( 'Manufacturer', 'threew-2025' ),
		__( 'Model', 'threew-2025' ),
		__( 'Trim', 'threew-2025' ),
	];

	const hasSelection = stepValues.some( Boolean );
	const statusMessage = hasSelection
		? sprintf(
				/* translators: 1: current step number (1-4). 2: translated label for the current step. */
				__( 'Step %1$d of 4: %2$s', 'threew-2025' ),
				activeIndex + 1,
				stepLabels[ activeIndex ]
		  )
		: __( 'Start with vehicle year', 'threew-2025' );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Selector copy', 'threew-2025' ) }>
					<TextControl
						label={ __( 'CTA Label', 'threew-2025' ) }
						value={ ctaLabel }
						onChange={ ( value ) =>
							setAttributes( { ctaLabel: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="threew-fitment-block__copy">
					<RichText
						tagName="h3"
						className="threew-fitment-block__headline"
						value={ headline }
						onChange={ ( value ) =>
							setAttributes( { headline: value } )
						}
						placeholder={ __(
							'Select Your Vehicle',
							'threew-2025'
						) }
					/>
					<RichText
						tagName="p"
						className="threew-fitment-block__subheadline"
						value={ subheadline }
						onChange={ ( value ) =>
							setAttributes( { subheadline: value } )
						}
						placeholder={ __(
							'Choose year, make, and model to see compatible parts.',
							'threew-2025'
						) }
					/>
				</div>

				<nav
					className="threew-fitment-block__progress"
					aria-label={ __(
						'Vehicle selection steps',
						'threew-2025'
					) }
					aria-live="polite"
				>
					<ol className="threew-fitment-progress__list">
						{ STEP_IDS.map( ( step, index ) => {
							const hasValue = Boolean( stepValues[ index ] );
							const isActive = index === activeIndex;
							const classes = [
								'threew-fitment-progress__step',
								hasValue ? 'is-complete' : '',
								isActive ? 'is-active' : '',
							]
								.filter( Boolean )
								.join( ' ' );

							return (
								<li
									key={ step }
									className={ classes }
									data-fitment-step={ step }
									aria-current={
										isActive ? 'step' : undefined
									}
								>
									<span className="threew-fitment-progress__index">
										{ index + 1 }
									</span>
									<span className="threew-fitment-progress__label">
										{ stepLabels[ index ] }
									</span>
								</li>
							);
						} ) }
					</ol>
					<p className="threew-fitment-progress__status">
						{ statusMessage }
					</p>
				</nav>

				<div className="threew-fitment-block__form-shell">
					<form
						className="threew-fitment-block__form"
						data-fitment-interactive="ready"
						aria-describedby="threew-fitment-helper"
						onSubmit={ ( event ) => event.preventDefault() }
						onReset={ handleReset }
						noValidate
					>
						<div className="threew-fitment-block__fields">
							<div className="threew-fitment-block__field">
								<label htmlFor="threew-fitment-year">
									{ __( 'Year', 'threew-2025' ) }
								</label>
								<select
									id="threew-fitment-year"
									value={ year }
									onChange={ handleYearChange }
								>
									<option value="" disabled>
										{ __( 'Select', 'threew-2025' ) }
									</option>
									{ getYears().map( ( y ) => (
										<option key={ y } value={ y }>
											{ y }
										</option>
									) ) }
								</select>
							</div>

							<div className="threew-fitment-block__field">
								<label htmlFor="threew-fitment-make">
									{ __( 'Manufacturer', 'threew-2025' ) }
								</label>
								<select
									id="threew-fitment-make"
									value={ make }
									onChange={ handleMakeChange }
									disabled={ ! makes.length }
								>
									<option value="" disabled>
										{ __( 'Select', 'threew-2025' ) }
									</option>
									{ makes.map( ( mk ) => (
										<option key={ mk } value={ mk }>
											{ mk }
										</option>
									) ) }
								</select>
							</div>

							<div className="threew-fitment-block__field">
								<label htmlFor="threew-fitment-model">
									{ __( 'Model', 'threew-2025' ) }
								</label>
								<select
									id="threew-fitment-model"
									value={ model }
									onChange={ handleModelChange }
									disabled={ ! models.length }
								>
									<option value="" disabled>
										{ __( 'Select', 'threew-2025' ) }
									</option>
									{ models.map( ( md ) => (
										<option key={ md } value={ md }>
											{ md }
										</option>
									) ) }
								</select>
							</div>

							<div className="threew-fitment-block__field">
								<label htmlFor="threew-fitment-trim">
									{ __( 'Trim', 'threew-2025' ) }
								</label>
								<select
									id="threew-fitment-trim"
									value={ trim }
									onChange={ ( event ) =>
										setTrim( event.target.value )
									}
									disabled={ ! trims.length }
								>
									<option value="" disabled>
										{ __( 'Select', 'threew-2025' ) }
									</option>
									{ trims.map( ( tr ) => (
										<option key={ tr } value={ tr }>
											{ tr }
										</option>
									) ) }
								</select>
							</div>
						</div>

						<div className="threew-fitment-block__actions">
							<button
								className="threew-fitment-block__submit"
								type="submit"
								disabled={ ! ( year && make && model ) }
							>
								{ ctaLabel ||
									__( 'Search Parts', 'threew-2025' ) }
							</button>
							<button
								className="threew-fitment-block__reset"
								type="reset"
								disabled={ ! hasSelection }
							>
								{ __( 'Clear vehicle', 'threew-2025' ) }
							</button>
						</div>
					</form>
					<p
						className="threew-fitment-block__helper"
						id="threew-fitment-helper"
					>
						{ __(
							'Start with vehicle year, then follow manufacturer, model, and trim.',
							'threew-2025'
						) }
					</p>
				</div>
			</div>
		</>
	);
}
