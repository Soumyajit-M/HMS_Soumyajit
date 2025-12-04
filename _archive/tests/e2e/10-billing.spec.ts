import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('billing create and fetch', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/billing.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Billing' })).toBeVisible();
  const p = await request.post('/api/patients.php', { form: { first_name: 'Bill', last_name: 'Patient', phone: '1234567890', date_of_birth: '1990-02-02', gender: 'male' } });
  const pj = await p.json();
  const bill = await request.post('/api/billing.php', {
    data: { patient_id: pj.patient_id, total_amount: 100, paid_amount: 50, notes: 'E2E', items: [{ item_name: 'Consultation', quantity: 1, unit_price: 100, total_price: 100 }] }
  });
  expect(bill.ok()).toBeTruthy();
  const bj = await bill.json();
  expect(bj.success).toBeTruthy();
  const list = await request.get('/api/billing.php');
  expect(list.ok()).toBeTruthy();
});
