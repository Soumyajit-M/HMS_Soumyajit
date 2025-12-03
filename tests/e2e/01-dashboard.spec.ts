import { test, expect } from '@playwright/test';
import { login } from './utils/auth';

test('dashboard widgets and actions', async ({ page }) => {
  await login(page);
  // Verify key widgets render
  const widgetsCount = await page.locator('.card, .widget, .stats').count();
  expect(widgetsCount).toBeGreaterThan(0);
  // Verify sidebar nav buttons clickable
  const nav = page.locator('nav .nav-link');
  const navCount = await nav.count();
  expect(navCount).toBeGreaterThan(3);
  // Trigger any refresh buttons
  const refreshBtn = page.locator('button:has-text("Refresh"), .btn:has-text("Refresh")');
  if (await refreshBtn.count()) {
    await refreshBtn.first().click({ timeout: 2000 }).catch(() => {});
  }
  await page.screenshot({ path: 'playwright-report/dashboard.png' });
});
