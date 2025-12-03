import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('rooms & beds page loads', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/rooms.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Rooms' })).toBeVisible();
  // API sanity
  const res = await request.get('/api/rooms.php');
  expect(res.ok()).toBeTruthy();
});
