import { RichText, useBlockProps } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { headline, subheadline, ctaLabel } = attributes;

	return (
		<div { ...useBlockProps.save( { className: 'threew-fitment-block' } ) }>
			<div className="threew-fitment-block__copy">
				<RichText.Content
					tagName="h3"
					className="threew-fitment-block__headline"
					value={ headline }
				/>
				<RichText.Content
					tagName="p"
					className="threew-fitment-block__subheadline"
					value={ subheadline }
				/>
			</div>
			<nav
				className="threew-fitment-block__progress"
				aria-label="Vehicle selection steps"
				aria-live="polite"
			>
				<ol className="threew-fitment-progress__list">
					<li
						className="threew-fitment-progress__step is-active"
						data-fitment-step="year"
					>
						<span className="threew-fitment-progress__index">
							1
						</span>
						<span className="threew-fitment-progress__label">
							{ 'Vehicle year' }
						</span>
					</li>
					<li
						className="threew-fitment-progress__step"
						data-fitment-step="make"
					>
						<span className="threew-fitment-progress__index">
							2
						</span>
						<span className="threew-fitment-progress__label">
							{ 'Manufacturer' }
						</span>
					</li>
					<li
						className="threew-fitment-progress__step"
						data-fitment-step="model"
					>
						<span className="threew-fitment-progress__index">
							3
						</span>
						<span className="threew-fitment-progress__label">
							{ 'Model' }
						</span>
					</li>
					<li
						className="threew-fitment-progress__step"
						data-fitment-step="trim"
					>
						<span className="threew-fitment-progress__index">
							4
						</span>
						<span className="threew-fitment-progress__label">
							{ 'Trim' }
						</span>
					</li>
				</ol>
				<p
					className="threew-fitment-progress__status"
					data-fitment-step-status
				>
					{ 'Start with vehicle year' }
				</p>
			</nav>
			<div className="threew-fitment-block__form-shell">
				<form
					className="threew-fitment-block__form"
					data-fitment-interactive="pending"
					aria-describedby="threew-fitment-helper"
					noValidate
				>
					<div className="threew-fitment-block__fields">
						<div className="threew-fitment-block__field">
							<label htmlFor="threew-fitment-year">
								{ 'Year' }
							</label>
							<select id="threew-fitment-year" disabled>
								<option value="" disabled selected>
									{ 'Select' }
								</option>
							</select>
						</div>
						<div className="threew-fitment-block__field">
							<label htmlFor="threew-fitment-make">
								{ 'Manufacturer' }
							</label>
							<select id="threew-fitment-make" disabled>
								<option value="" disabled selected>
									{ 'Select' }
								</option>
							</select>
						</div>
						<div className="threew-fitment-block__field">
							<label htmlFor="threew-fitment-model">
								{ 'Model' }
							</label>
							<select id="threew-fitment-model" disabled>
								<option value="" disabled selected>
									{ 'Select' }
								</option>
							</select>
						</div>
						<div className="threew-fitment-block__field">
							<label htmlFor="threew-fitment-trim">
								{ 'Trim' }
							</label>
							<select id="threew-fitment-trim" disabled>
								<option value="" disabled selected>
									{ 'Select' }
								</option>
							</select>
						</div>
					</div>
					<div className="threew-fitment-block__actions">
						<button
							className="threew-fitment-block__submit"
							type="submit"
							disabled
						>
							{ ctaLabel || 'Search Parts' }
						</button>
						<button
							className="threew-fitment-block__reset"
							type="reset"
							disabled
						>
							{ 'Clear vehicle' }
						</button>
					</div>
				</form>
				<p
					className="threew-fitment-block__helper"
					id="threew-fitment-helper"
				>
					{
						'Start with vehicle year, then follow manufacturer, model, and trim.'
					}
				</p>
			</div>
		</div>
	);
}
