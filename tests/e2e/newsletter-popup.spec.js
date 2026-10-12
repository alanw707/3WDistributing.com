const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const popupScript = fs.readFileSync(
  path.resolve(__dirname, '../../deploy/production/3w-newsletter-popup/assets/newsletter-popup.js'),
  'utf8'
);

function fixtureHtml() {
  return `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <style>
    body { margin: 0; min-height: 100vh; }
    #bg-add { position: fixed; left: 20px; bottom: 40px; padding: 16px 28px; }
    #threew-newsletter-popup[hidden] { display: none; }
    #threew-newsletter-popup { position: fixed; inset: 0; z-index: 99999; display: grid; place-items: start end; padding: 24px; background: rgba(10, 14, 20, 0.34); }
    .threew-np-dialog { width: 360px; padding: 20px; background: white; }
  </style>
</head>
<body>
  <button id="bg-add" type="button">Add To Cart</button>
  <div id="threew-newsletter-popup" hidden>
    <div class="threew-np-dialog" role="dialog" aria-modal="true" aria-labelledby="threew-np-title">
      <button id="threew-newsletter-popup-close" type="button" aria-label="Close newsletter signup">×</button>
      <h2 id="threew-np-title">Get 5% off</h2>
      <form id="threew-newsletter-popup-form">
        <label>Email <input type="email" name="email" required></label>
        <label><input type="checkbox" name="consent" value="1" required> Consent</label>
        <button type="submit">Get coupon</button>
        <p id="threew-newsletter-popup-status" aria-live="polite"></p>
      </form>
    </div>
  </div>
  <script>
    window.__bgClicks = 0;
    document.getElementById('bg-add').addEventListener('click', function () { window.__bgClicks++; });
    window.threewNewsletterPopup = {
      delaySeconds: 1,
      suppressionDays: 30,
      consentCookie: 'threew_consent_v1',
      nonce: 'test',
      ajaxUrl: '/noop'
    };
    setTimeout(function () { document.getElementById('bg-add').focus(); }, 0);
  </script>
  <script>${popupScript}</script>
</body>
</html>`;
}

async function openFixture(page) {
  await page.route('http://newsletter.test/fixture', route => route.fulfill({
    status: 200,
    contentType: 'text/html',
    body: fixtureHtml(),
  }));
  await page.context().addCookies([
    { name: 'threew_consent_v1', value: 'test', url: 'http://newsletter.test' },
  ]);
  await page.goto('http://newsletter.test/fixture');
  await expect(page.locator('#threew-newsletter-popup')).toBeVisible({ timeout: 5000 });
}

test('newsletter signup request contract remains intact', async ({ page }) => {
  await page.route('http://newsletter.test/noop', route => route.fulfill({
    status: 200,
    contentType: 'application/json',
    body: JSON.stringify({
      success: true,
      data: { couponCode: 'TEST5', discountPercent: 5 },
    }),
  }));
  await openFixture(page);

  await page.locator('[name="email"]').fill('customer@example.com');
  await page.locator('[name="consent"]').check();
  await page.locator('#threew-newsletter-popup-form button[type="submit"]').click();

  await expect(page.locator('.threew-np-success')).toContainText('Success. Use code TEST5 for 5% off.');
});

for (const viewport of [
  { name: 'desktop', width: 1280, height: 800 },
  { name: 'mobile', width: 390, height: 844 },
]) {
  test.describe(viewport.name, () => {
    test.use({ viewport });

    test('Escape closes, traps focus, and restores the trigger', async ({ page }) => {
      await openFixture(page);

      await expect(page.locator('#threew-newsletter-popup-close')).toBeFocused();
      await page.keyboard.press('Shift+Tab');
      await expect(page.locator('#threew-newsletter-popup-form button[type="submit"]')).toBeFocused();
      await page.keyboard.press('Tab');
      await expect(page.locator('#threew-newsletter-popup-close')).toBeFocused();

      await page.keyboard.press('Escape');
      await expect(page.locator('#threew-newsletter-popup')).toBeHidden();
      await expect(page.locator('#bg-add')).toBeFocused();

      await page.reload();
      await page.waitForTimeout(1500);
      await expect(page.locator('#threew-newsletter-popup')).toBeHidden();
    });

    test('a backdrop click dismisses without activating the page behind', async ({ page }) => {
      await openFixture(page);
      const addButton = page.locator('#bg-add');
      const box = await addButton.boundingBox();

      await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
      await expect(page.locator('#threew-newsletter-popup')).toBeHidden();
      await expect.poll(() => page.evaluate(() => window.__bgClicks)).toBe(0);

      await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
      await expect.poll(() => page.evaluate(() => window.__bgClicks)).toBe(1);
    });
  });
}
