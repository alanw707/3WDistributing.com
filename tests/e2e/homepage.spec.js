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

test('Mobile hero title fits within the viewport', async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 812 });
  await page.goto('/');

  const hero = page.locator('.threew-hero--fullbleed');
  const title = page.locator('#threew-hero-title');
  await expect(hero).toBeVisible();
  await expect(title).toBeVisible();

  const [heroBox, titleBox, fontSize] = await Promise.all([
    hero.boundingBox(),
    title.boundingBox(),
    title.evaluate((el) => Number.parseFloat(window.getComputedStyle(el).fontSize)),
  ]);

  expect(heroBox).not.toBeNull();
  expect(titleBox).not.toBeNull();
  expect(titleBox.x).toBeGreaterThanOrEqual(heroBox.x);
  expect(titleBox.x + titleBox.width).toBeLessThanOrEqual(heroBox.x + heroBox.width + 1);
  expect(titleBox.width).toBeLessThanOrEqual(375);
  expect(fontSize).toBeLessThanOrEqual(32);
});
