import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('telemedicine sessions create and fetch', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/telemedicine.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Telemedicine' })).toBeVisible();
  const p = await request.post('/api/patients.php', { form: { first_name: 'Tele', last_name: 'Patient', phone: '1234567890', date_of_birth: '1994-03-03', gender: 'female' } });
  const pj = await p.json();
  const s = await request.post('/api/telemedicine.php', { data: { patient_id: pj.patient_id, doctor_id: 1, notes: 'E2E session' } });
  expect(s.ok()).toBeTruthy();
  const sessions = await request.get('/api/telemedicine.php');
  expect(sessions.ok()).toBeTruthy();
});
