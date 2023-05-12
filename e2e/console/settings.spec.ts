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

    consoleTest('Users: adding blog user', async ({testingApi, console, page}) => {
        await testingApi.factory.blogFull();
        await console.visitAndNav('settings');
        await page.getByRole('link', { name: 'Users' }).click();
        await page.getByRole('button', { name: 'Add' }).click();
        await page.getByText('Guest User').click();
        await page.getByLabel('Name').click();
        await page.getByLabel('Name').fill('test');
        await page.getByRole('button', { name: 'Create', exact: true }).click();
        await expect(page.getByText('testtestGUEST0')).toBeVisible();
    });

    consoleTest('Adding language', async ({testingApi, console, page}) => {
        await testingApi.factory.blogFull();
        await console.visitAndNav('settings');
        await page.getByRole('link', { name: 'Languages' }).click();
        await page.getByRole('button', { name: 'New' }).click();
        await page.getByPlaceholder('English').fill('french');
        await page.getByPlaceholder('English').press('Tab');
        await page.getByPlaceholder('en', { exact: true }).fill('fr');
        await page.getByRole('button', { name: 'Add' }).click();
        await expect(page.getByText('french')).toBeVisible();
    });

});