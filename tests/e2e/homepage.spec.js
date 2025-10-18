const { test, expect } = require('@playwright/test');

test('Homepage loads hero, trust strip, categories, and blog teaser', async ({ page }) => {
  await page.goto('/');

  await expect(page.locator('.threew-hero--fullbleed')).toBeVisible();
  await expect(page.locator('.threew-trust-strip__item')).toHaveCount(3);
  await expect(page.locator('.threew-category-grid__tile')).toHaveCount(6);

  const blogCards = await page.locator('.threew-blog-card').count();
  expect(blogCards).toBeGreaterThan(0);
});
