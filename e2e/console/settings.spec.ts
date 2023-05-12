import { expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";


test.describe('settings', () => {

    consoleTest('General: blog renaming', async ({testingApi, console, page}) => {
        await testingApi.factory.blogFull();
        await console.visitAndNav('settings');

        await expect(page.getByText('General Settings')).toBeVisible();

        await page.locator('#input-name').click();
        await page.locator('#input-name').fill('MyAwesome Blog');
        await page.getByRole('button', { name: 'SAVE' }).click();
        await expect(page.locator('#input-name')).toHaveValue('MyAwesome Blog');
    });

});