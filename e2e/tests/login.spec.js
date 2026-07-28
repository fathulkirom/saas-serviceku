import { test, expect } from '@playwright/test';

const DEMO_EMAIL = 'demo@serviceku.app';
const DEMO_OWNER_EMAIL = 'owner@serviceku.app';

test.describe('Login Flow', () => {
  test('should display login page with store search', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('h1')).toContainText('ServiceKU');
    await expect(page.getByText('Masuk ke Toko Anda')).toBeVisible();
  });

  test('should show dev quick login buttons in local environment', async ({ page }) => {
    await page.goto('/');
    // Dev mode should show quick login section
    await expect(page.getByText('Quick Login')).toBeVisible();
  });

  test('should have register link', async ({ page }) => {
    await page.goto('/');
    const registerLink = page.getByText('Daftar di sini');
    await expect(registerLink).toBeVisible();
    await expect(registerLink).toHaveAttribute('href', /register/);
  });
});
