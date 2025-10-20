const { test, expect } = require('@playwright/test');

test('Homepage loads hero, trust strip, categories, and blog teaser', async ({ page }) => {
  await page.goto('/');

  await expect(page.locator('.threew-hero--fullbleed')).toBeVisible();
  await expect(page.locator('.threew-trust-strip__item')).toHaveCount(3);
  await expect(page.locator('.threew-category-grid__tile')).toHaveCount(6);

  const logo = page.locator('.threew-header__branding-logo img').first();
  await expect(logo).toBeVisible();
  const primaryLogoHeight = await logo.evaluate((el) =>
    Math.round(el.getBoundingClientRect().height)
  );
  expect(primaryLogoHeight).toBeGreaterThanOrEqual(44);

  await page.evaluate(() => window.scrollTo(0, 200));
  await page.waitForFunction(() =>
    document.querySelector('.threew-header.threew-header--compact')
  );
  await page.waitForTimeout(300);
  const compactLogoHeight = await logo.evaluate((el) =>
    Math.round(el.getBoundingClientRect().height)
  );
  expect(compactLogoHeight).toBeGreaterThanOrEqual(36);

  const blogCards = await page.locator('.threew-blog-card').count();
  expect(blogCards).toBeGreaterThan(0);
});
