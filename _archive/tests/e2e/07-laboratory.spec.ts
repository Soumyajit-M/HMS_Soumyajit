import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('laboratory test orders and results', async ({ page, request }) => {
  await login(page);
  await page.goto('/laboratory.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Laboratory' })).toBeVisible();
  const res = await request.get('/api/laboratory.php');
  expect(res.ok()).toBeTruthy();
});
