import { test, expect } from '@playwright/test';

const DEMO_EMAIL = 'demo@serviceku.app';
const DEMO_OWNER_EMAIL = 'owner@serviceku.app';

test.describe('Login Flow', () => {
  test('should display landing page with hero content', async ({ page }) => {
    await page.goto('/');
    // Landing page hero (h1) — redesign baru, bukan lagi "ServiceKU"
    await expect(page.locator('h1')).toContainText('Kelola Servis');
    // Ada tombol login
    await expect(page.getByRole('link', { name: 'Masuk' }).first()).toBeVisible();
  });

  test('should have login link', async ({ page }) => {
    await page.goto('/');
    const loginLink = page.getByRole('link', { name: 'Masuk' }).first();
    await expect(loginLink).toBeVisible();
    await expect(loginLink).toHaveAttribute('href', /login/);
  });

  test('should have register link', async ({ page }) => {
    await page.goto('/');
    const registerLink = page.getByRole('link', { name: /Daftar|Mulai/i }).first();
    await expect(registerLink).toBeVisible();
    await expect(registerLink).toHaveAttribute('href', /register/);
  });
});
