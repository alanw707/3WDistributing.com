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
					<label>{'Year'}</label>
					<select disabled>
						<option value="">{'Select'}</option>
					</select>
				</div>
				<div className="threew-fitment-block__field">
					<label>{'Make'}</label>
					<select disabled>
						<option value="">{'Select'}</option>
					</select>
				</div>
				<div className="threew-fitment-block__field">
					<label>{'Model'}</label>
					<select disabled>
						<option value="">{'Select'}</option>
					</select>
				</div>
				<div className="threew-fitment-block__field">
					<label>{'Trim'}</label>
					<select disabled>
						<option value="">{'Select'}</option>
					</select>
				</div>
				<button className="threew-fitment-block__submit" type="button" disabled>
					{ctaLabel || 'Search Parts'}
				</button>
			</div>
		</div>
	);
}
