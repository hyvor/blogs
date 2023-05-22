import { expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";


test.describe('Settings', () => {

    consoleTest('General', async ({testingApi, console, page}) => {

        // Database seeding
        const blog = await testingApi.factory.blogFull();
        await testingApi.factory.language({blog_id: blog.blog.id, name: 'English', code: 'en', is_primary: false});

        await console.visitAndNav('settings');

        await expect(page.getByText('General Settings')).toBeVisible();

        // Fill main language
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

        // Fill secondary language
        await page.locator('span').filter({ hasText: 'en' }).first().click();
        await page.waitForTimeout(2000);
        await page.locator('#input-name').fill('EnglishBlog');
        await page.locator('#input-description').fill('EnglishDescription');
        await page.getByRole('button', { name: 'SAVE' }).click();
        await page.reload();

        // Test main language
        await expect(page.locator('#input-name')).toHaveValue('MyAwesome Blog');
        await expect(page.locator('#input-description')).toHaveValue('MyAwesomeDescription');
        await expect(page.locator('#input-blog-facebook')).toHaveValue('facebook');
        await expect(page.locator('#input-blog-twitter')).toHaveValue('twitter');
        await expect(page.locator('#input-blog-linkedin')).toHaveValue('linkedin');
        await expect(page.locator('#input-blog-youtube')).toHaveValue('youtube');
        await expect(page.locator('#input-blog-tiktok')).toHaveValue('tiktok');
        await expect(page.locator('#input-blog-instagram')).toHaveValue('instagram');
        await expect(page.locator('#input-github')).toHaveValue('github');

        // Test secondary language
        await page.locator('span').filter({ hasText: 'en' }).first().click();
        await expect(page.locator('#input-name')).toHaveValue('EnglishBlog');
        await expect(page.locator('#input-description')).toHaveValue('EnglishDescription');
    });

    test.describe('Languages', () => {
        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            const blog = await testingApi.factory.blogFull();
            await testingApi.factory.language({blog_id: blog.blog.id, name: 'English', code: 'en', is_primary: false});
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Languages' }).click();
          });
          
        consoleTest('Adding language', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'New' }).click();
            await page.getByPlaceholder('English').fill('french');
            await page.getByPlaceholder('English').press('Tab');
            await page.getByPlaceholder('en', { exact: true }).fill('fr');
            await page.getByRole('button', { name: 'Add' }).click();
            await expect(page.getByText('french')).toBeVisible();
        });

        consoleTest('Editing language', async ({testingApi, console, page}) => {
            await page.getByRole('button').nth(3).click();
            await page.getByPlaceholder('English').fill('French');
            await page.getByPlaceholder('en', { exact: true }).click();
            await page.getByPlaceholder('en', { exact: true }).fill('fr');
            await page.getByRole('button', { name: 'Update' }).click();
            await expect(page.getByText('french')).toBeVisible();
        });

        consoleTest('Deleting language', async ({testingApi, console, page}) => {await page.getByRole('button').nth(4).click();
            await page.getByPlaceholder('Type language code').click();
            await page.getByPlaceholder('Type language code').fill('en');
            await page.getByRole('button', { name: 'Delete' }).click();
            await expect(page.getByText('English')).not.toBeVisible();
        });
    })


});