import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('appointments page and list/create', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/appointments.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Appointments' })).toBeVisible();
  const list = await request.get('/api/appointments.php');
  expect(list.ok()).toBeTruthy();
});
