import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('reports page and revenue stats', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/reports.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Reports' })).toBeVisible();
  const res = await request.get('/api/reports-api.php?action=revenue');
  expect(res.ok()).toBeTruthy();
});
