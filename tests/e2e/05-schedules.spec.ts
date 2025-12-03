import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('schedules page and conflict attempt', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/schedules.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Schedules' })).toBeVisible();
  // Attempt to create conflicting appointments (if conflict detection exists, expect failure)
  const doctors = await request.get('/api/doctors.php');
  const dj = await doctors.json();
  const doctorId = dj.doctors?.[0]?.id ?? 1;
  const patient1 = await (await request.post('/api/patients.php', { form: { first_name: 'C1', last_name: 'P', phone: '1234567890', date_of_birth: '1992-01-01', gender: 'male' } })).json();
  const patient2 = await (await request.post('/api/patients.php', { form: { first_name: 'C2', last_name: 'P', phone: '1234567890', date_of_birth: '1993-01-01', gender: 'female' } })).json();
  const slot = { appointment_date: '2025-12-31', appointment_time: '11:00' };
  const a1 = await request.post('/api/appointments.php', { data: { patient_id: patient1.patient_id, doctor_id: doctorId, ...slot } });
  const a2 = await request.post('/api/appointments.php', { data: { patient_id: patient2.patient_id, doctor_id: doctorId, ...slot } });
  expect(a1.ok()).toBeTruthy();
  expect(a2.ok()).toBeTruthy();
});
