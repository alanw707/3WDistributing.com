import { __ } from '@wordpress/i18n';
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

				<div
					className="threew-fitment-block__progress"
					aria-hidden="true"
				>
					<div
						className="threew-fitment-progress__step is-active"
						data-fitment-step="year"
					>
						<span className="threew-fitment-progress__index">
							1
						</span>
						<span className="threew-fitment-progress__label">
							{ __( 'Vehicle year', 'threew-2025' ) }
						</span>
					</div>
					<span
						className="threew-fitment-progress__connector"
						aria-hidden="true"
					/>
					<div
						className="threew-fitment-progress__step"
						data-fitment-step="make"
					>
						<span className="threew-fitment-progress__index">
							2
						</span>
						<span className="threew-fitment-progress__label">
							{ __( 'Manufacturer', 'threew-2025' ) }
						</span>
					</div>
					<span
						className="threew-fitment-progress__connector"
						aria-hidden="true"
					/>
					<div
						className="threew-fitment-progress__step"
						data-fitment-step="model"
					>
						<span className="threew-fitment-progress__index">
							3
						</span>
						<span className="threew-fitment-progress__label">
							{ __( 'Model', 'threew-2025' ) }
						</span>
					</div>
					<span
						className="threew-fitment-progress__connector"
						aria-hidden="true"
					/>
					<div
						className="threew-fitment-progress__step"
						data-fitment-step="trim"
					>
						<span className="threew-fitment-progress__index">
							4
						</span>
						<span className="threew-fitment-progress__label">
							{ __( 'Trim', 'threew-2025' ) }
						</span>
					</div>
				</div>

				<div
					className="threew-fitment-block__form-shell"
					role="group"
					aria-label={ __(
						'Vehicle fitment selector',
						'threew-2025'
					) }
				>
					<div
						className="threew-fitment-block__form"
						data-fitment-interactive="pending"
					>
						<div className="threew-fitment-block__field">
							<label htmlFor="threew-fitment-year">
								{ __( 'Year', 'threew-2025' ) }
							</label>
							<select
								id="threew-fitment-year"
								value={ year }
								onChange={ handleYearChange }
							>
								<option value="" disabled selected>
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
								<option value="" disabled selected>
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
								<option value="" disabled selected>
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
								<option value="" disabled selected>
									{ __( 'Select', 'threew-2025' ) }
								</option>
								{ trims.map( ( tr ) => (
									<option key={ tr } value={ tr }>
										{ tr }
									</option>
								) ) }
							</select>
						</div>

						<button
							className="threew-fitment-block__submit"
							type="button"
						>
							{ ctaLabel || __( 'Search Parts', 'threew-2025' ) }
						</button>
					</div>
					<p className="threew-fitment-block__helper">
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
