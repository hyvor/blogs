import { expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";
import path from 'path';


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
    });

    test.describe('Users', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            const blog = await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Users' }).click();
        });

        consoleTest('Adding user', async ({testingApi, console, page}) => {
            // TODO: test the user
        });

    });

    test.describe('Tags', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            const blog = await testingApi.factory.blogFull();
            await testingApi.factory.routes({blog_id: blog.blog.id, name: 'tag'});
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Tags' }).click();
        });

        consoleTest('Add tag', async ({testingApi, console, page}) => {
        await page.getByRole('button', { name: 'Create', exact: true }).click();
        await page.getByPlaceholder('Blogging').fill('TestTag');
        await page.getByRole('button', { name: 'Create' }).nth(2).click();
        

        await expect(page.locator('#middle').getByText('TestTag', { exact: true })).toBeVisible();
    });

        consoleTest('Tag modification', async ({testingApi, console, page}) => {
            // Create a tag
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('Blogging').fill('TestTag');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.waitForTimeout(1000);

            await page.getByRole('button').nth(2).click();
            await page.getByLabel('Name').fill('TestTagModified');
            await page.getByRole('button', { name: 'Update' }).click();
            await page.reload();


            await expect(page.locator('#middle').getByText('TestTagModified', { exact: true })).toBeVisible();
        });

        consoleTest('Tag deletion', async ({testingApi, console, page}) => {
            // Create a tag
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('Blogging').fill('TestTag');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.waitForTimeout(1000);

            await page.getByRole('button').nth(3).click();
            await page.getByRole('button', { name: 'Delete' }).click();

            await expect(page.locator('#middle').getByText('TestTag', { exact: true })).not.toBeVisible();
        });

    });

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

    test.describe('Light & Dark Modes', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Light & Dark modes' }).click();
          });

        consoleTest('Light color radio', async ({testingApi, console, page}) => {
            await page.locator('div:nth-child(2) > .radio-container > .checkmark').first().click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.locator('div:nth-child(2) > .radio-container > .checkmark')).toBeChecked();
        });

        consoleTest('Dark color radio', async ({testingApi, console, page}) => {
            await page.locator('label').filter({ hasText: 'Dark' }).locator('span').nth(1).click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.locator('label').filter({ hasText: 'Dark' }).locator('span').nth(1)).toBeChecked();
        });

        consoleTest('Default color mode light radio', async ({testingApi, console, page}) => {
            await page.locator('div:nth-child(3) > .dual-right > div > div:nth-child(2) > .radio-container > .checkmark').click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.locator('div:nth-child(3) > .dual-right > div > div:nth-child(2) > .radio-container > .checkmark')).toBeChecked();
        });

        consoleTest('Default color mode dark radio', async ({testingApi, console, page}) => {
            await page.locator('div:nth-child(3) > .dual-right > div > div:nth-child(3) > .radio-container > .checkmark').click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.locator('div:nth-child(3) > .dual-right > div > div:nth-child(3) > .radio-container > .checkmark')).toBeChecked();
        });
    });

    test.describe('Navigation', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Navigation' }).click();
          });

        consoleTest('Add Header Navigation', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('About us').fill('Header');
            await page.getByPlaceholder('/about').click();
            await page.getByPlaceholder('/about').fill('/google.fr');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();

            await expect(page.getByText('Header/google.fr')).toBeVisible();
        });

        consoleTest('Add Footer Navigation', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('About us').fill('Footer');
            await page.getByPlaceholder('/about').click();
            await page.getByPlaceholder('/about').fill('/google.fr');
            await page.locator('.input-view > .react-select > .react-select__control > .react-select__value-container > .react-select__input-container').click();
            await page.getByText('Footer', { exact: true }).click();
            await page.getByRole('button', { name: 'Create' }).nth(2).click();

            await expect(page.getByText('Footer/google.fr')).toBeVisible();
        });

        consoleTest('Edit Navigation (from Footer to Header)', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('About us').fill('Footer');
            await page.getByPlaceholder('/about').click();
            await page.getByPlaceholder('/about').fill('/google.fr');
            await page.locator('.input-view > .react-select > .react-select__control > .react-select__value-container > .react-select__input-container').click();
            await page.getByText('Footer', { exact: true }).click();
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            // Wait few time for the creation
            await page.waitForTimeout(1000);

            await page.getByRole('button').nth(2).click();
            await page.getByPlaceholder('About us').click();
            await page.getByPlaceholder('About us').fill('Header');
            await page.locator('.input-view > .react-select > .react-select__control > .react-select__value-container > .react-select__input-container').click();
            await page.getByText('Header', { exact: true }).click();
            await page.getByRole('button', { name: 'Update' }).click();

            await expect(page.getByText('Header/google.fr')).toBeVisible();
        });

        consoleTest('Delete Navigation', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('About us').fill('Footer');
            await page.getByPlaceholder('/about').click();
            await page.getByPlaceholder('/about').fill('/google.fr');
            await page.locator('.input-view > .react-select > .react-select__control > .react-select__value-container > .react-select__input-container').click();
            await page.getByText('Footer', { exact: true }).click();
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            // Wait few time for the creation
            await page.waitForTimeout(1000);

            await page.getByRole('button').nth(3).click();
            await page.getByRole('button', { name: 'Delete' }).click();

            await expect(page.getByText('Footer/google.fr')).not.toBeVisible();
        });
    });

    test.describe('Media', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Media' }).click();
          });

        consoleTest('Upload File', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Upload', exact: true }).click();
            const cwd = process.cwd();
            await page.locator('input[type=file]').first().setInputFiles(cwd +'/e2e/baseTest.ts');
            await page.getByRole('button', { name: 'Upload' }).click();

            await expect(page.getByText('baseTest.ts')).toBeVisible();
        });
    });

    test.describe('Redirects', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Redirects' }).click();
          });

        consoleTest('Add Redirect', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('/welcome').fill('/welcome');
            await page.getByPlaceholder('https://hyvor.com').fill('https://www.google.com/');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();

            await expect(page.getByText('/welcome')).toBeVisible();
        });

        consoleTest('Edit Redirect', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('/welcome').fill('/welcome');
            await page.getByPlaceholder('https://hyvor.com').fill('https://www.google.com/');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            // Wait few time for the creation
            await page.waitForTimeout(1000);

            await page.getByRole('button').nth(2).click();
            await page.getByPlaceholder('/welcome').fill('/youtube');
            await page.getByPlaceholder('https://hyvor.com').fill('https://www.youtube.com/');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();

            await expect(page.getByText('/youtube')).toBeVisible();
        });

        consoleTest('Delete Redirect', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('/welcome').fill('/welcome');
            await page.getByPlaceholder('https://hyvor.com').fill('https://www.google.com/');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            // Wait few time for the creation
            await page.waitForTimeout(1000);

            await page.getByRole('button').nth(3).click();
            await page.getByRole('button', { name: 'Delete' }).click();

            await expect(page.getByText('/welcome')).not.toBeVisible();
        });
    });

    test.describe('Routes', () => {
        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Routes' }).click();
          });

          consoleTest('Add Route', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('New Route').fill('TestRoute');
            await page.getByPlaceholder('/path').fill('/path');
            await page.getByPlaceholder('template').fill('template');
            await page.getByPlaceholder('A FilterQ expression...').fill('exp');
            await page.getByPlaceholder('text/html').fill('text/html');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();

            // TODO: create issue that the create modal does not close
            await expect(page.getByText('TestRoute')).toBeVisible();
          });

          consoleTest('Modify Route', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('New Route').fill('TestRoute');
            await page.getByPlaceholder('/path').fill('/path');
            await page.getByPlaceholder('template').fill('template');
            await page.getByPlaceholder('A FilterQ expression...').fill('exp');
            await page.getByPlaceholder('text/html').fill('text/html');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.reload();

            await page.getByRole('button').nth(2).click();
            await page.getByPlaceholder('/path').fill('/newPath');
            await page.getByRole('button', { name: 'Update' }).click();

            await expect(page.getByText('/newPath')).toBeVisible();
          });

        consoleTest('Delete Route', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('New Route').fill('TestRoute');
            await page.getByPlaceholder('/path').fill('/path');
            await page.getByPlaceholder('template').fill('template');
            await page.getByPlaceholder('A FilterQ expression...').fill('exp');
            await page.getByPlaceholder('text/html').fill('text/html');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.reload();

            await page.getByRole('button').nth(3).click();
            await page.getByRole('button', { name: 'Delete' }).click();

            await expect(page.getByText('TestRoute')).not.toBeVisible();
        });
    });

    test.describe('API Keys', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'API Keys' }).click();
          });

        consoleTest('Add API Key', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('My API Key').fill('TestApi');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.reload();

            await expect(page.getByText('TestApi')).toBeVisible();
        });

        consoleTest('Copy API Key', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('My API Key').fill('TestApi');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.reload();

            await page.getByRole('button').nth(2).click();

            const clipboardText = await page.evaluate(() => navigator.clipboard.readText());
            expect(clipboardText.length).toBeGreaterThan(0);
        });

        consoleTest('Delete API Key', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('My API Key').fill('TestApi');
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.reload();

            await page.getByRole('button').nth(3).click();
            await page.getByRole('button', { name: 'Delete' }).click();

            await expect(page.getByText('TestApi')).not.toBeVisible();
        });
    });

    test.describe('Webhooks', () => {

        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Webhooks' }).click();
          });

        consoleTest('Add Webhook', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('Webhook URL').click({
              modifiers: ['Control']
            });
            await page.getByPlaceholder('Webhook URL').fill('https://www.google.com/');
            await page.getByText('cache.single').click();
            await page.getByText('cache.templates').click();
            await page.getByText('cache.all').click();
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.reload();

            await expect(page.getByText('https://www.google.com/')).toBeVisible();

            await page.getByRole('button').nth(2).click();

            const clipboardText = await page.evaluate(() => navigator.clipboard.readText());
            expect(clipboardText.length).toBeGreaterThan(0);
        });

        consoleTest('Edit Webhook', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('Webhook URL').click({
              modifiers: ['Control']
            });
            await page.getByPlaceholder('Webhook URL').fill('https://www.google.com/');
            await page.getByText('cache.single').click();
            await page.getByText('cache.templates').click();
            await page.getByText('cache.all').click();
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.reload();

            await page.getByRole('button').nth(3).click();
            await page.getByPlaceholder('Webhook URL').fill('https://www.youtube.com/');

            await page.getByRole('button', { name: 'Update' }).click();

            await expect(page.getByText('https://www.youtube.com/')).toBeVisible();
        });

        consoleTest('Delete Webhook', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Create', exact: true }).click();
            await page.getByPlaceholder('Webhook URL').click({
              modifiers: ['Control']
            });
            await page.getByPlaceholder('Webhook URL').fill('https://www.google.com/');
            await page.getByText('cache.single').click();
            await page.getByText('cache.templates').click();
            await page.getByText('cache.all').click();
            await page.getByRole('button', { name: 'Create' }).nth(2).click();
            await page.reload();

            await page.getByRole('button').nth(4).click();
            await page.getByRole('button', { name: 'Delete' }).click();

            await expect(page.getByText('https://www.google.com/')).not.toBeVisible();
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

    test.describe('Custom Code', () => {
        
        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Custom Code' }).click();
          });

          consoleTest('Add head code', async ({testingApi, console, page}) => {
            await page.getByRole('textbox').first().fill('Header code');
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.getByText('Header code')).toBeVisible();
        });

        consoleTest('Add footer code', async ({testingApi, console, page}) => {
            await page.getByRole('textbox').nth(1).fill('Footer code');
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.getByText('Footer code')).toBeVisible();
        });

        consoleTest('Disable flashload', async ({testingApi, console, page}) => {
            await page.getByTestId('switch').locator('div').nth(2).click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();
        
            await expect(page.getByTestId('switch')).toHaveAttribute('class', 'unchecked');
        });
        
    });

    test.describe('Syntax Highlighting', () => {
        
        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Syntax Highlighting' }).click();
          });

          consoleTest('Disable Syntax Highlighting', async ({testingApi, console, page}) => {
            await page.locator('.react-switch-handle').first().click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.getByTestId('switch')).toHaveAttribute('class', 'unchecked');
        });

        consoleTest('Changing theme', async ({testingApi, console, page}) => {
            await page.locator('#middle svg').nth(1).click();
            await page.getByText('monokai', { exact: true }).click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.locator('div').filter({ hasText: /^monokai$/ }).nth(2)).toBeVisible();
        });

        consoleTest('Deactivate line number', async ({testingApi, console, page}) => {
            await page.locator('div:nth-child(4) > .dual-right > span > div > .react-switch-handle').click();
            await page.getByRole('button', { name: 'SAVE' }).click();
            await page.reload();

            await expect(page.getByTestId('switch').nth(1)).toHaveAttribute('class', 'unchecked');
        });
        
    });

    test.describe('Export', () => {
        
        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull();
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Export' }).click();
          });

          consoleTest('Export data', async ({testingApi, console, page}) => {
            await page.getByRole('button', { name: 'Export Now' }).click();
            await page.getByRole('button', { name: 'Export Now' }).nth(1).click();

            await expect(page.getByText('Hyvor Blogs JSON').first()).toBeVisible();
        });
        
    });


    test.describe('Danger', () => {
        
        consoleTest.beforeEach(async ({testingApi, console, page}) => {
            await testingApi.factory.blogFull({blogAttrs: {subdomain: 'test'}});
            await console.visitAndNav('settings');
            await page.getByRole('link', { name: 'Danger' }).click();
          });

          consoleTest('Delete blog', async ({testingApi, console, page}) => {
           await page.getByRole('button', { name: 'Delete Blog' }).click();
           await page.getByPlaceholder('test').fill('test');
           await page.getByRole('button', { name: 'Delete', exact: true }).click();
           await page.getByRole('button', { name: 'OK' }).click(); 

           await expect(page.getByRole('heading', { name: 'Multi-language blogging platform' })).toBeVisible();
        });
        
    });
});