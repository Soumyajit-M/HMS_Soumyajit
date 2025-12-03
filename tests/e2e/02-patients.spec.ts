import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('patient registration and retrieval', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/patients.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Patients' })).toBeVisible();

  const payload = {
    first_name: 'E2E', last_name: 'Patient', email: 'e2e.patient@example.com', phone: '1234567890',
    date_of_birth: '1990-01-01', gender: 'male'
  };
  const res = await request.post('/api/patients.php', { form: payload });
  expect(res.ok()).toBeTruthy();
  const json = await res.json();
  expect(json.success).toBeTruthy();
  const patientId = json.patient_id;

  const getRes = await request.get(`/api/patients.php`);
  expect(getRes.ok()).toBeTruthy();
  const listJson = await getRes.json();
  expect(listJson.success).toBeTruthy();
  const found = (listJson.patients || []).some((p: any) => p.patient_id === patientId);
  expect(found).toBeTruthy();
  await page.screenshot({ path: 'playwright-report/patients.png' });
});

test('patient negative: invalid phone rejected', async ({ request }) => {
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  const res = await request.post('/api/patients.php', {
    form: { first_name: 'Bad', last_name: 'Phone', phone: 'abc', date_of_birth: '1990-01-01', gender: 'other' }
  });
  expect(res.ok()).toBeTruthy();
  const json = await res.json();
  expect(json.success).toBeFalsy();
});
