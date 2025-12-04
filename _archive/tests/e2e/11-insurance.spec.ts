import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('insurance page and claims list', async ({ page, request }) => {
  await login(page);
  await page.goto('/insurance.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Insurance' })).toBeVisible();
  const providers = await request.get('/api/insurance.php?action=providers');
  expect(providers.ok()).toBeTruthy();
  const claims = await request.get('/api/insurance.php?action=claims');
  expect(claims.ok()).toBeTruthy();
});

