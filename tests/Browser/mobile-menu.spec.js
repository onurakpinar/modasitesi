import { test, expect } from '@playwright/test';

const MOBILE_WIDTHS = [320, 360, 375, 390, 430, 768];
const DESKTOP_WIDTHS = [1024, 1440];

function menuButton(page) {
    return page.getByRole('button', { name: 'Menüyü aç veya kapat' });
}

async function expectMenuClosed(page) {
    await expect(page.locator('#site-navigation-mobile')).toBeHidden();
    await expect(page.locator('#site-mobile-menu-overlay')).toBeHidden();
    await expect(page.locator('#site-navigation-mobile-categories')).toBeHidden();
    await expect(menuButton(page)).toHaveAttribute('aria-expanded', 'false');
    expect(await page.evaluate(() => document.body.classList.contains('overflow-hidden'))).toBe(false);
}

async function expectMenuOpen(page) {
    await expect(page.locator('#site-navigation-mobile')).toBeVisible();
    await expect(page.locator('#site-mobile-menu-overlay')).toBeVisible();
    await expect(menuButton(page)).toHaveAttribute('aria-expanded', 'true');
    expect(await page.evaluate(() => document.body.classList.contains('overflow-hidden'))).toBe(true);
}

async function expectNoHorizontalScroll(page) {
    const metrics = await page.evaluate(() => ({
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
    }));

    expect(metrics.scrollWidth).toBeLessThanOrEqual(metrics.clientWidth + 1);
}

async function openMobileMenu(page) {
    await menuButton(page).click();
    await expectMenuOpen(page);
}

test.describe('Mobil menü', () => {
    test.beforeEach(async ({ page }) => {
        page.on('console', (message) => {
            if (message.type() === 'error') {
                throw new Error(`Konsol hatası: ${message.text()}`);
            }
        });

        page.on('pageerror', (error) => {
            throw error;
        });
    });

    for (const width of MOBILE_WIDTHS) {
        test(`[${width}px] ilk yükleme ve temel etkileşimler`, async ({ page }) => {
            await page.setViewportSize({ width, height: 844 });
            await page.goto('/', { waitUntil: 'networkidle' });

            await expect(page.locator('script[type="module"][src*="/build/assets/app"]')).toHaveCount(1);
            await expect(page.locator('#site-navigation-mobile')).toHaveCount(1);
            await expectMenuClosed(page);

            const mainOpacity = await page.locator('#main-content').evaluate((el) => {
                const overlay = document.querySelector('#site-mobile-menu-overlay');
                return {
                    mainVisible: el.offsetParent !== null || getComputedStyle(el).display !== 'none',
                    overlayDisplay: overlay ? getComputedStyle(overlay).display : 'none',
                };
            });

            expect(mainOpacity.overlayDisplay).toBe('none');

            await openMobileMenu(page);
            await menuButton(page).click();
            await expectMenuClosed(page);

            await openMobileMenu(page);
            await page.locator('#site-mobile-menu-overlay').click({ position: { x: 10, y: 10 } });
            await expectMenuClosed(page);

            await openMobileMenu(page);
            await page.keyboard.press('Escape');
            await expectMenuClosed(page);

            await openMobileMenu(page);
            const categoriesButton = page.locator('#site-navigation-mobile').getByRole('button', { name: 'Kategoriler' });
            await categoriesButton.click();
            await expect(categoriesButton).toHaveAttribute('aria-expanded', 'true');
            await expect(page.locator('#site-navigation-mobile-categories')).toBeVisible();
            await expect(page.locator('#site-navigation-mobile-categories').getByRole('link', { name: 'Kadın Modası' })).toBeVisible();

            const chevron = categoriesButton.locator('svg');
            await expect(chevron).toHaveClass(/rotate-180/);

            await categoriesButton.click();
            await expect(categoriesButton).toHaveAttribute('aria-expanded', 'false');
            await expect(chevron).not.toHaveClass(/rotate-180/);
            await expect(page.locator('#site-navigation-mobile-categories')).toBeHidden();

            await page.locator('#site-navigation-mobile').getByRole('link', { name: 'Yazılar' }).click();
            await page.waitForURL('**/yazilar');
            await expectMenuClosed(page);

            await expectNoHorizontalScroll(page);
        });
    }

    test('[390px] menü scroll ve Ara bağlantısı tam görünür', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 500 });
        await page.goto('/', { waitUntil: 'networkidle' });
        await openMobileMenu(page);

        const mobileNav = page.locator('#site-navigation-mobile');
        const categoriesButton = mobileNav.getByRole('button', { name: 'Kategoriler' });
        await categoriesButton.click();

        const araLink = mobileNav.getByRole('link', { name: 'Ara', exact: true });
        await araLink.scrollIntoViewIfNeeded();
        await expect(araLink).toBeInViewport();

        const navMetrics = await mobileNav.evaluate((el) => ({
            scrollHeight: el.scrollHeight,
            clientHeight: el.clientHeight,
            overflowY: getComputedStyle(el).overflowY,
        }));

        expect(navMetrics.overflowY).toBe('auto');
        expect(navMetrics.scrollHeight).toBeGreaterThanOrEqual(navMetrics.clientHeight);
    });

    test('[390px] mobil menü açıkken desktop genişliğine geçiş', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/', { waitUntil: 'networkidle' });
        await openMobileMenu(page);

        await page.setViewportSize({ width: 1440, height: 900 });
        await page.waitForTimeout(150);

        await expect(menuButton(page)).toBeHidden();
        await expect(page.locator('#site-navigation-mobile')).toBeHidden();
        await expect(page.locator('#site-mobile-menu-overlay')).toBeHidden();
        expect(await page.evaluate(() => document.body.classList.contains('overflow-hidden'))).toBe(false);
        await expect(page.locator('#site-navigation-desktop')).toBeVisible();
    });

    for (const width of DESKTOP_WIDTHS) {
        test(`[${width}px] masaüstü navigasyon ve dropdown`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.goto('/', { waitUntil: 'networkidle' });

            await expect(menuButton(page)).toBeHidden();
            await expect(page.locator('#site-navigation-mobile')).toBeHidden();
            await expect(page.locator('#site-mobile-menu-overlay')).toBeHidden();

            const desktopNav = page.locator('#site-navigation-desktop');
            await expect(desktopNav).toBeVisible();

            const categoriesButton = desktopNav.getByRole('button', { name: 'Kategoriler' });
            await expect(categoriesButton).toHaveAttribute('aria-expanded', 'false');
            await expect(page.locator('#site-navigation-categories')).toBeHidden();

            await categoriesButton.click();
            await expect(categoriesButton).toHaveAttribute('aria-expanded', 'true');
            await expect(page.locator('#site-navigation-categories')).toBeVisible();

            await categoriesButton.click();
            await expect(categoriesButton).toHaveAttribute('aria-expanded', 'false');
            await expect(page.locator('#site-navigation-categories')).toBeHidden();

            await categoriesButton.click();
            await page.locator('main').click({ position: { x: 20, y: 20 } });
            await expect(page.locator('#site-navigation-categories')).toBeHidden();

            await categoriesButton.click();
            await page.keyboard.press('Escape');
            await expect(page.locator('#site-navigation-categories')).toBeHidden();
        });
    }

    test('Alpine tek kez başlatılıyor ve x-cloak tanımlı', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/', { waitUntil: 'networkidle' });

        const alpineState = await page.evaluate(() => ({
            alpineDefined: typeof window.Alpine !== 'undefined',
            cloakRule: [...document.styleSheets].some((sheet) => {
                try {
                    return [...sheet.cssRules].some((rule) => rule.cssText?.includes('[x-cloak]'));
                } catch {
                    return false;
                }
            }),
        }));

        expect(alpineState.alpineDefined).toBe(true);
        expect(alpineState.cloakRule).toBe(true);
    });
});
