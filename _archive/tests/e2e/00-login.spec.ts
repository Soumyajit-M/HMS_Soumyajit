import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('login works and dashboard loads', async ({ page }) => {
  await login(page);
  await expect(page.locator('h1,h2').filter({ hasText: 'Dashboard' })).toBeVisible();
});
