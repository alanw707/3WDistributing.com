const { test, expect } = require('@playwright/test');

const mobileConfig = {
  viewport: { width: 390, height: 844 },
  userAgent:
    'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
  hasTouch: true,
  isMobile: true,
};

test.describe('SEO intro accordion (mobile)', () => {
  test.use(mobileConfig);

  test('collapsed by default, expands on tap', async ({ page }) => {
    await page.goto('/');

    const details = page.locator('.threew-seo-accordion');
    const summary = page.locator('summary.threew-seo-accordion__summary');
    const content = page.locator('.threew-seo-accordion__content');

    await expect(details).toBeVisible();
    await expect(details).not.toHaveAttribute('open', /.+/);

    // Hint label should be hidden on small screens
    const hint = page.locator('.threew-seo-accordion__hint');
    if (await hint.count()) {
      await expect(hint).toBeHidden();
    }

    await summary.click();
    await expect(details).toHaveJSProperty('open', true);

    // Verify content becomes visible and animated styles applied
    await expect(content).toBeVisible();
    await expect(content).toHaveCSS('opacity', '1');
    const rect = await content.boundingBox();
    expect(rect?.height || 0).toBeGreaterThan(40);
  });

  test('toggle with keyboard (Enter)', async ({ page }) => {
    await page.goto('/');
    const details = page.locator('.threew-seo-accordion');
    const summary = page.locator('summary.threew-seo-accordion__summary');

    await expect(details).toHaveJSProperty('open', false);
    await summary.focus();
    await page.keyboard.press('Enter');
    await expect(details).toHaveJSProperty('open', true);

    // Close again
    await page.keyboard.press('Enter');
    await expect(details).toHaveJSProperty('open', false);
  });
});
