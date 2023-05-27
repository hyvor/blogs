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

    test.describe('Hosting', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Hosting' }).click();
          });

        consoleTest('Subdomain modification', async ({testingApi, console, page}) => {
            await page.getByLabel('', { exact: true }).fill('subdomain');
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();
            await expect(page.getByText('subdomain')).toBeVisible(); 
        });

        consoleTest('Custom domain', async ({testingApi, console, page}) => {
            await page.locator('label').filter({ hasText: 'Custom Domain' }).locator('span').nth(1).click();
            await page.locator('#input-custom-domain').fill('https://www.google.com/');
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();
            await expect(page.locator('label').filter({ hasText: 'Subdomain' }).locator('span').nth(1)).not.toBeChecked();
            await expect(page.locator('label').filter({ hasText: 'Custom Domain' }).locator('span').nth(1)).toBeChecked();
            await expect(page.locator('#input-custom-domain')).toHaveValue('https://www.google.com/');
        });

        consoleTest('Self-hosting', async ({testingApi, console, page}) => {
            await page.locator('label').filter({ hasText: 'Self-hosting' }).locator('span').nth(1).click();
            await page.locator('#input-self-hosting-url').fill('https://www.google.com/');
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();
            await expect(page.locator('label').filter({ hasText: 'Subdomain' }).locator('span').nth(1)).not.toBeChecked();
            await expect(page.locator('label').filter({ hasText: 'Self-hosting' }).locator('span').nth(1)).toBeChecked();
            await expect(page.locator('#input-self-hosting-url')).toHaveValue('https://www.google.com/');
        });

        consoleTest('Embeddable', async ({testingApi, console, page}) => {
            await page.getByTestId('switch').locator('div').nth(2).click();
            await page.locator('#input-self-hosting-url').fill('https://www.google.com/');
            await page.locator('#input-self-hosting-url').click({
              modifiers: ['Control']
            });
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();
            await expect(page.locator('#input-self-hosting-url')).toHaveValue('https://www.google.com/');

        });
    });

    test.describe('SEO', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'SEO' }).click();
          });

        consoleTest('Allow indexing switch', async ({testingApi, console, page}) => {
            await page.getByTestId('switch').locator('div').nth(2).click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.getByText('Search engines won\'t index your blog!')).toBeVisible();
        });

        consoleTest('External links type radio', async ({testingApi, console, page}) => {
            await page.locator('label').filter({ hasText: 'Nofollow' }).locator('span').nth(1).click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.locator('label').filter({ hasText: 'Nofollow' }).locator('span').nth(1)).toBeChecked();
        });

        consoleTest('Robot.txt custom', async ({testingApi, console, page}) => {
            await page.locator('pre').filter({ hasText: 'Disallow: /p/' }).click();
            await page.getByRole('textbox').fill('test');
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.locator('pre').filter({ hasText: 'Disallow: /p/' })).toHaveText('Disallow: /p/test');
        });

    });

    test.describe('Comments & Newsletter', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Comments & Newsletter' }).click();
          });

        consoleTest('Comments Embed Code', async ({testingApi, console, page}) => {
            await page.locator('.CodeMirror-lines').first().click();
            await page.getByRole('textbox').first().fill('Test code');
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.getByText('Test code')).toBeVisible();
        });

        consoleTest('Newsletter Singup Form Code', async ({testingApi, console, page}) => {
            await page.locator('pre').nth(3).click();
            await page.getByRole('textbox').nth(1).fill('Test code2');
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.getByText('Test code2')).toBeVisible();
        });
    });

});