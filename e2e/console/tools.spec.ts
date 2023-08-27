import { expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";

test.describe('Export', () => {
        
    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.blogFull();
        await console.visitAndNav('tools');
        await page.getByRole('link', { name: 'Export' }).click();
      });

      consoleTest('Export data', async ({testingApi, console, page}) => {
        await page.getByRole('button', { name: 'Export Now' }).click();
        await page.getByRole('button', { name: 'Export Now' }).nth(1).click();

        await expect(page.getByText('Hyvor Blogs JSON').first()).toBeVisible();
    });
    
});