import { expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";


test.describe('settings', () => {

    consoleTest('General', async ({testingApi, console, page}) => {
        await testingApi.factory.blogFull();
        await console.visitAndNav('settings');

        await expect(page.getByText('General Settings')).toBeVisible();

        await page.locator('#input-name').fill('MyAwesome Blog');

        await page.locator('#input-description').fill('MyAwesomeDescription');

        await page.locator('#input-blog-facebook').fill('facebook');
        await page.locator('#input-blog-twitter').fill('twitter');
        await page.locator('#input-blog-linkedin').fill('linkedin');
        await page.locator('#input-blog-youtube').fill('youtube');
        await page.locator('#input-blog-tiktok').fill('tiktok');
        await page.locator('#input-blog-instagram').fill('instagram');
        await page.locator('#input-github').fill('github');

        await page.getByRole('button', { name: 'SAVE' }).click();
        await page.reload();

        await expect(page.locator('#input-name')).toHaveValue('MyAwesome Blog');
        await expect(page.locator('#input-description')).toHaveValue('MyAwesomeDescription');
        await expect(page.locator('#input-blog-facebook')).toHaveValue('facebook');
        await expect(page.locator('#input-blog-twitter')).toHaveValue('twitter');
        await expect(page.locator('#input-blog-linkedin')).toHaveValue('linkedin');
        await expect(page.locator('#input-blog-youtube')).toHaveValue('youtube');
        await expect(page.locator('#input-blog-tiktok')).toHaveValue('tiktok');
        await expect(page.locator('#input-blog-instagram')).toHaveValue('instagram');
        await expect(page.locator('#input-github')).toHaveValue('github');
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