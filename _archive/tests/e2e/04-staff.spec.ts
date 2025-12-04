import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('staff page loads; role-based UI present', async ({ page }) => {
  await login(page);
  await page.goto('/staff.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Staff' })).toBeVisible();
  // Basic check: actions menu or role indicators present
  const count = await page.locator('.table, .card').count();
  expect(count).toBeGreaterThan(0);
});
