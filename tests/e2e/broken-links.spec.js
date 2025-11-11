// @ts-check
const { test, expect } = require('@playwright/test');

const BASE_URL =
	process.env.PLAYWRIGHT_PROD_BASE_URL ||
	process.env.PLAYWRIGHT_BASE_URL ||
	'https://www.3wdistributing.com';

/**
 * Recursively crawl sitemap indexes and return concrete page URLs.
 *
 * @param {import('@playwright/test').APIRequestContext} request
 * @param {string} sitemapUrl
 * @param {Set<string>} seen
 * @returns {Promise<string[]>}
 */
async function collectSitemapUrls(request, sitemapUrl, seen = new Set()) {
	const urls = [];
	if (seen.has(sitemapUrl)) {
		return urls;
	}

	seen.add(sitemapUrl);

	try {
		const response = await request.get(sitemapUrl, { timeout: 20000 });
		if (!response.ok()) {
			return urls;
		}

		const xml = await response.text();
		const matches = [...xml.matchAll(/<loc>(.*?)<\/loc>/gi)];

		for (const match of matches) {
			const value = (match[1] || '').trim();
			if (!value) {
				continue;
			}

			let absoluteUrl = value;
			try {
				absoluteUrl = new URL(value, sitemapUrl).href;
			} catch (_) {
				// Fall back to raw string if URL constructor fails.
			}

			if (absoluteUrl.endsWith('.xml')) {
				const nested = await collectSitemapUrls(request, absoluteUrl, seen);
				urls.push(...nested);
			} else {
				urls.push(absoluteUrl);
			}
		}
	} catch (error) {
		console.warn(`⚠️  Unable to read sitemap ${sitemapUrl}: ${error.message}`);
	}

	return urls;
}

/**
 * Gather unique internal links found on the current page.
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} baseOrigin
 * @returns {Promise<{ url: string; text: string }[]>}
 */
async function collectInternalLinks(page, baseOrigin) {
	const anchors = await page.$$eval(
		'a[href]',
		(links, origin) =>
			links
				.map((link) => {
					const raw = (link.getAttribute('href') || '').trim();
					if (
						!raw ||
						raw.startsWith('#') ||
						raw.startsWith('mailto:') ||
						raw.startsWith('tel:') ||
						raw.startsWith('javascript:')
					) {
						return null;
					}

					try {
						const resolved = new URL(raw, window.location.href);
						return {
							url: resolved.href,
							text: (link.textContent || '').trim(),
						};
					} catch (_) {
						return null;
					}
				})
				.filter(Boolean)
				.filter((link) => link && link.url.startsWith(origin)),
		baseOrigin
	);

	const map = new Map();
	for (const anchor of anchors) {
		if (!map.has(anchor.url)) {
			map.set(anchor.url, anchor);
		}
	}
	return Array.from(map.values());
}

test.describe('Production broken-link scan', () => {
	test('sitemap pages load without broken in-site links', async ({ page, request }) => {
		test.setTimeout(180000);

		const baseOrigin = new URL(BASE_URL).origin;
		const sitemapUrl = `${baseOrigin}/sitemap.xml`;
		const sitemapTargets = await collectSitemapUrls(request, sitemapUrl);
		const crawlTargets = sitemapTargets.filter((url) => url.startsWith(baseOrigin));

		if (crawlTargets.length === 0) {
			crawlTargets.push(baseOrigin);
		}

		const brokenLinks = [];

		for (const target of crawlTargets) {
			await page.goto(target, { waitUntil: 'networkidle' });
			const links = await collectInternalLinks(page, baseOrigin);

			for (const link of links) {
				try {
					const response = await request.get(link.url, { timeout: 20000 });
					if (!response.ok()) {
						brokenLinks.push({
							page: target,
							link: link.url,
							status: response.status(),
						});
					}
				} catch (error) {
					brokenLinks.push({
						page: target,
						link: link.url,
						status: 'network-error',
						message: error.message,
					});
				}
			}
		}

		if (brokenLinks.length) {
			console.table(brokenLinks);
		}

		expect(
			brokenLinks,
			`Broken links found:\n${brokenLinks
				.map((entry) => `${entry.status} – ${entry.link} (from ${entry.page})`)
				.join('\n')}`
		).toEqual([]);
	});
});
