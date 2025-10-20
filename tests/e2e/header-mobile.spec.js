const { test, expect } = require('@playwright/test');

const mobileConfig = {
	viewport: { width: 390, height: 844 },
	userAgent:
		'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
	hasTouch: true,
	isMobile: true,
};

test.describe('Mobile header navigation', () => {
	test.use(mobileConfig);

	test('drawer sits above overlay and links are interactable', async ({ page }) => {
		await page.goto('/');

		const toggle = page.getByRole('button', { name: /menu/i });
		await expect(toggle).toBeVisible();

		await toggle.click();

		const header = page.locator('.threew-header');
		await expect(header).toHaveAttribute('data-nav-open', 'true');

		const firstLink = page.locator('.threew-header__menu a').first();
		await expect(firstLink).toBeVisible();

		const overlay = page.locator('.threew-header__overlay');
		await expect(overlay).toHaveClass(/is-active/);

		const diagnostics = await page.evaluate(() => {
			const header = document.querySelector('.threew-header');
			const drawer = header?.querySelector('.threew-header__drawer');
			const overlay = header?.querySelector('.threew-header__overlay');
			const firstLink = drawer?.querySelector('.threew-header__menu a');
			if (!header || !drawer || !overlay || !firstLink) {
				return null;
			}

			const linkRect = firstLink.getBoundingClientRect();
			const cx = linkRect.left + linkRect.width / 2;
			const cy = linkRect.top + linkRect.height / 2;
			const topElement = document.elementFromPoint(cx, cy);
			const drawerStyles = drawer
				? window.getComputedStyle(drawer)
				: null;
			const maxHeightRaw =
				drawerStyles?.getPropertyValue(
					'--threew-header-drawer-max-height',
				) ?? '';
			const maxHeightValue = maxHeightRaw.trim().endsWith('px')
				? Number.parseFloat(maxHeightRaw)
				: null;

			return {
				navOpen: header.dataset.navOpen,
				bodyScrollLocked: document.body.classList.contains('threew-body--nav-open'),
				overlayActive: overlay.classList.contains('is-active'),
				overlayZ: window.getComputedStyle(overlay).zIndex,
				headerZ: window.getComputedStyle(header).zIndex,
				drawerZ: window.getComputedStyle(drawer).zIndex,
				drawerPositioned: window.getComputedStyle(drawer).position,
				linkPointerEvents: window.getComputedStyle(firstLink).pointerEvents,
				elementAtCenterTag: topElement?.tagName ?? null,
				elementAtCenterClass: topElement?.className ?? null,
				drawerOverflowY: drawerStyles?.overflowY ?? null,
				drawerMaxHeightVar: maxHeightRaw.trim(),
				drawerMaxHeightPx: maxHeightValue,
				drawerClientHeight: drawer?.clientHeight ?? null,
			};
		});

		expect(diagnostics).not.toBeNull();
		expect(diagnostics.overlayActive).toBe(true);
		expect(diagnostics.elementAtCenterClass || '').not.toContain('threew-header__overlay');
		expect(diagnostics.drawerPositioned).toBe('relative');
		expect(['auto', 'scroll']).toContain(diagnostics.drawerOverflowY);
		expect(diagnostics.drawerMaxHeightPx).not.toBeNull();
		expect(diagnostics.drawerMaxHeightPx).toBeGreaterThan(0);
		if (typeof diagnostics.drawerClientHeight === 'number' && diagnostics.drawerClientHeight > 0) {
			expect(diagnostics.drawerClientHeight).toBeLessThanOrEqual(diagnostics.drawerMaxHeightPx + 1);
		}
	});

	test('first primary link responds to tap without the drawer collapsing', async ({ page }) => {
		await page.goto('/');

		const toggle = page.getByRole('button', { name: /menu/i });
		await toggle.click();

		const header = page.locator('.threew-header');
		const firstLink = page.locator('.threew-header__menu a').first();

		await expect(header).toHaveAttribute('data-nav-open', 'true');
		await expect(firstLink).toBeVisible();

		await firstLink.click({ trial: true, timeout: 2000 });
		await expect(header).toHaveAttribute('data-nav-open', 'true');
	});
});
