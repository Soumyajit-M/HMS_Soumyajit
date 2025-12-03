import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('doctor list and appointment scheduling', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/doctors.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Doctors' })).toBeVisible();

  // Ensure there is a doctor (create one via API if none)
  const docList = await request.get('/api/doctors.php');
  let docJson = await docList.json();
  if (!docJson.success || !docJson.doctors?.length) {
    const newDoc = { first_name: 'E2E', last_name: 'Doctor', email: 'doctor@example.com', phone: '0000000000', specialization: 'General' };
    const create = await request.post('/api/doctors.php', { data: newDoc });
    expect(create.ok()).toBeTruthy();
    docJson = await create.json();
    expect(docJson.success).toBeTruthy();
  }
  const doctorId = (docJson.doctors?.[0]?.id) ?? docJson.doctor_id;

  // Create a patient for appointment
  const p = await request.post('/api/patients.php', { form: { first_name: 'Appt', last_name: 'Patient', phone: '1234567890', date_of_birth: '1991-01-01', gender: 'female' } });
  const pj = await p.json();
  const patientId = pj.patient_id;

  // Schedule an appointment
  const appt = await request.post('/api/appointments.php', {
    data: {
      patient_id: patientId,
      doctor_id: doctorId,
      appointment_date: '2025-12-31',
      appointment_time: '10:00',
      reason: 'Routine'
    }
  });
  expect(appt.ok()).toBeTruthy();
  const aj = await appt.json();
  expect(aj.success).toBeTruthy();
});
