import { RichText, useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { headline, subheadline, ctaLabel } = attributes;

	return (
		<div {...useBlockProps.save({ className: 'threew-fitment-block' })}>
			<div className="threew-fitment-block__copy">
				<RichText.Content
					tagName="h3"
					className="threew-fitment-block__headline"
					value={headline}
				/>
				<RichText.Content
					tagName="p"
					className="threew-fitment-block__subheadline"
					value={subheadline}
				/>
			</div>
			<div className="threew-fitment-block__form" data-fitment-interactive="pending">
				<div className="threew-fitment-block__field">
					<label htmlFor="threew-fitment-year">{'Year'}</label>
					<select id="threew-fitment-year" disabled>
						<option value="">{'Select'}</option>
					</select>
				</div>
				<div className="threew-fitment-block__field">
					<label htmlFor="threew-fitment-make">{'Make'}</label>
					<select id="threew-fitment-make" disabled>
						<option value="">{'Select'}</option>
					</select>
				</div>
				<div className="threew-fitment-block__field">
					<label htmlFor="threew-fitment-model">{'Model'}</label>
					<select id="threew-fitment-model" disabled>
						<option value="">{'Select'}</option>
					</select>
				</div>
				<div className="threew-fitment-block__field">
					<label htmlFor="threew-fitment-trim">{'Trim'}</label>
					<select id="threew-fitment-trim" disabled>
						<option value="">{'Select'}</option>
					</select>
				</div>
				<button className="threew-fitment-block__submit" type="button" disabled>
					{ctaLabel || 'Search Parts'}
				</button>
			</div>
			<p className="threew-fitment-block__helper">
				{'Begin with year to unlock make, model, and trim options.'}
			</p>
		</div>
	);
}
