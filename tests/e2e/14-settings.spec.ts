import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('settings get and set', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/settings.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Settings' })).toBeVisible();
  const setRes = await request.post('/api/set_setting.php', { data: { key: 'general_testflag', value: 'true' } });
  expect(setRes.ok()).toBeTruthy();
  const getRes = await request.get('/api/settings.php');
  expect(getRes.ok()).toBeTruthy();
});
