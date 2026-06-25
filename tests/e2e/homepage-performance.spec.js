const { test, expect } = require('@playwright/test');

test.use({
  viewport: { width: 390, height: 844 },
  isMobile: true,
  hasTouch: true,
});

test('Homepage mobile keeps critical path lean', async ({ page }) => {
  await page.goto('/', { waitUntil: 'domcontentloaded' });

  const assetUrls = await page
    .locator('link[rel="stylesheet"], script[src]')
    .evaluateAll((nodes) =>
      nodes.map((node) => node.href || node.src).filter(Boolean)
    );

  expect(assetUrls).not.toEqual(
    expect.arrayContaining([
      expect.stringContaining('/plugins/woocommerce/assets/css/woocommerce'),
      expect.stringContaining('/plugins/woocommerce/assets/css/brands'),
      expect.stringContaining('/plugins/woocommerce/assets/js/frontend/woocommerce'),
      expect.stringContaining('/plugins/woocommerce/assets/js/frontend/add-to-cart'),
      expect.stringContaining('/plugins/woocommerce/assets/js/sourcebuster'),
      expect.stringContaining('/plugins/woocommerce/assets/js/frontend/order-attribution'),
    ])
  );

  await expect(
    page.locator(
      'link[rel="preload"][as="image"][href*="hero-motorsport-mobile"][href$=".webp"]'
    )
  ).toHaveAttribute('fetchpriority', 'high');
});
