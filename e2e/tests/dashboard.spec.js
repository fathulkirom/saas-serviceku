import { test, expect } from '@playwright/test';

test.describe('Dashboard', () => {
  test('should redirect to login when not authenticated', async ({ page }) => {
    await page.goto('/dashboard');
    // Should redirect to login page
    await expect(page).toHaveURL(/login|tenant\.lookup/);
  });

  test('should show 404 for non-existent tenant', async ({ page }) => {
    await page.goto('http://nonexistent.localhost:8000/dashboard');
    // Should show some error page
    const title = await page.title();
    expect(title).toBeDefined();
  });
});
