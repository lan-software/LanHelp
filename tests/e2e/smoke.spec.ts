import { expect, test } from '@playwright/test';

test('home page loads without console errors', async ({ page }) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => {
        consoleErrors.push(error.message);
    });

    const response = await page.goto('/');

    expect(response?.status()).toBeLessThan(500);
    expect(consoleErrors, `unexpected console errors: ${consoleErrors.join('\n')}`).toEqual([]);
});
