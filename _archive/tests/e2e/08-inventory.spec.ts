import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('inventory page and stock item create', async ({ page, request }) => {
  await login(page);
  await request.post('/index.php', { form: { username: 'admin', password: 'password' } });
  await page.goto('/inventory.php');
  await expect(page.locator('h1,h2').filter({ hasText: 'Inventory' })).toBeVisible();
  const res = await request.post('/api/inventory.php', {
    form: { item_name: 'E2E Item', category: 'Test', quantity: 5, unit_price: 10, supplier: 'Test Supplier' }
  });
  expect(res.ok()).toBeTruthy();
  const list = await request.get('/api/inventory.php');
  expect(list.ok()).toBeTruthy();
});
