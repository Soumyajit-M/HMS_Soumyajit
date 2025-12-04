import { Page } from '@playwright/test';

export async function login(page: Page, username = 'admin', password = 'password') {
  await page.goto('/');
  await page.fill('#username', username);
  await page.fill('#password', password);
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard.php');
}
