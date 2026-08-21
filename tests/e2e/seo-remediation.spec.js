// @ts-check
const { test, expect } = require('@playwright/test');

const BASE_URL =
	process.env.PLAYWRIGHT_PROD_BASE_URL ||
	process.env.PLAYWRIGHT_BASE_URL ||
	'https://www.3wdistributing.com';

test.describe('SEO remediation', () => {
	test('overlapping BRABUS price articles redirect to the selected hubs', async ({ request }) => {
		const redirects = new Map([
			['/brabus-g-wagon-price/', '/price-of-g-wagon-brabus/'],
			['/brabus-price/', '/price-of-g-wagon-brabus/'],
			['/mercedes-benz-g63-brabus-price/', '/g63-brabus-price/'],
		]);

		for (const [source, destination] of redirects) {
			const response = await request.get(`${BASE_URL}${source}`, {
				maxRedirects: 0,
			});
			expect(response.status(), source).toBe(301);
			expect(response.headers().location, source).toBe(`${BASE_URL}${destination}`);
		}
	});

	test('main sitemap excludes utility and duplicate commerce URLs', async ({ request }) => {
		const response = await request.get(`${BASE_URL}/sitemap-pages.xml`);
		expect(response.ok()).toBeTruthy();
		const xml = await response.text();

		for (const path of ['/shop/', '/cart/', '/checkout/', '/my-account/']) {
			expect(xml, path).not.toContain(`<loc>${BASE_URL}${path}</loc>`);
		}
	});

	test('retained price guides have current metadata and commercial actions', async ({ page }) => {
		for (const path of ['/price-of-g-wagon-brabus/', '/g63-brabus-price/']) {
			await page.goto(`${BASE_URL}${path}`, { waitUntil: 'domcontentloaded' });
			await expect(page).toHaveTitle(/2026/);
			await expect(page.locator('[data-threew-commercial-cta]')).toBeVisible();
			await expect(page.locator('[data-threew-commercial-cta] a[href*="shop.3wdistributing.com/product-category/brabus"]')).toBeVisible();
			await expect(page.locator('[data-threew-commercial-cta] a[href*="shop.3wdistributing.com/contact-us"]')).toBeVisible();
		}
	});
});
