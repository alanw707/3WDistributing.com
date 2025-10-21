import { test, expect } from '@playwright/test';

const BASE_URL = process.env.THREEW_BASE_URL ?? 'http://localhost:8080/';

test.describe('Mobile menu access', () => {
  test('menu opens after scrolling', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(BASE_URL, { waitUntil: 'networkidle' });

    // Scroll deep down the page to simulate the reported failure case.
    await page.waitForLoadState('domcontentloaded');
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight * 0.6));

    // Open the mobile menu.
    await page.getByRole('button', { name: /menu/i }).click();

    const drawer = page.locator('#threew-header-drawer');
    await expect(drawer).toBeVisible();

    await page.screenshot({
      path: 'tests/playwright/screenshots/mobile-menu-open.png',
      fullPage: true,
    });
  });
});
