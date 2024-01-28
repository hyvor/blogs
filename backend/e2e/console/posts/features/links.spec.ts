import { expect, test } from "@playwright/test";
import {consoleTest} from "../../consoleTest.ts";

test.describe('Links', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Add a valid link', async ({testingApi, console, page}) => {
        // Mock check-URL API call
        await page.route('*/**/check-urls*', async route => {
            const json = [{"id":1,"url":"https:\/\/www.google.fr\/","full_url":"https:\/\/www.google.fr\/","status_code":200,"status_type":"ok","ignored":false,"post_id":1,"post_variant_id":1,"post_variant_language_id":1,"post_variant_title":"Test Post"}];
            await route.fulfill({ json });
          });

        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('https://www.google.fr/');
        await page.keyboard.press('Enter');

        await page.getByRole('button', { name: 'Links' }).click();
        await expect(page.getByText('Links (1)')).toBeVisible();
        await expect(page.getByText('https://www.google.fr/external')).toBeVisible();
        await expect(page.locator('span').filter({ hasText: 'OK' }).nth(2)).toBeVisible();
        await expect(page.locator('span').filter({ hasText: 'OKOK - HTTP status 200' }).getByRole('img')).toBeVisible();
    });

    consoleTest('Refresh a link', async ({testingApi, console, page}) => {
        // Mock check-URL API call
        await page.route('*/**/check-urls*', async route => {
            const json = [{"id":1,"url":"https:\/\/www.google.fr\/","full_url":"https:\/\/www.google.fr\/","status_code":200,"status_type":"ok","ignored":false,"post_id":1,"post_variant_id":1,"post_variant_language_id":1,"post_variant_title":"Test Post"}];
            await route.fulfill({ json });
          });
        
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('https://www.google.fr/');
        await page.keyboard.press('Enter');

        await page.getByRole('button', { name: 'Links' }).click();
        await page.locator('span').filter({ hasText: 'Recheck' }).getByRole('button').click();
        await expect(page.getByText('Links (1)')).toBeVisible();
        await expect(page.getByText('https://www.google.fr/external')).toBeVisible();
    });

    consoleTest('Delete a link', async ({testingApi, console, page}) => {
        // Mock check-URL API call
        await page.route('*/**/check-urls*', async route => {
            const json = [{"id":1,"url":"https:\/\/www.google.fr\/","full_url":"https:\/\/www.google.fr\/","status_code":200,"status_type":"ok","ignored":false,"post_id":1,"post_variant_id":1,"post_variant_language_id":1,"post_variant_title":"Test Post"}];
            await route.fulfill({ json });
          });

        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('https://www.google.fr/');
        await page.keyboard.press('Enter');

        await page.getByRole('button', { name: 'Links' }).click();
        await page.locator('span').filter({ hasText: 'Edit in Editor' }).getByRole('button').click();
        await page.keyboard.press('Backspace');
        await expect(page.getByText('Links (1)')).not.toBeVisible();
        await expect(page.getByText('https://www.google.fr/external')).not.toBeVisible();
    });

    consoleTest('Ignrore a link', async ({testingApi, console, page}) => {
        // Mock check-URL API call
        await page.route('*/**/check-urls*', async route => {
            const json = [{"id":1,"url":"https:\/\/www.google.fr\/","full_url":"https:\/\/www.google.fr\/","status_code":200,"status_type":"ok","ignored":false,"post_id":1,"post_variant_id":1,"post_variant_language_id":1,"post_variant_title":"Test Post"}];
            await route.fulfill({ json });
          });
        // Mock ignore-link API call
        await page.route('*/**/ignore-link*', async route => {
            const json = {"id":1,"url":"https:\/\/www.google.fr\/","full_url":"https:\/\/www.google.fr\/","status_code":200,"status_type":"ok","ignored":true,"post_id":1,"post_variant_id":1,"post_variant_language_id":1,"post_variant_title":"Test Post"};
            await route.fulfill({ json });
        });

        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('https://www.google.fr/');
        await page.keyboard.press('Enter');

        await page.getByRole('button', { name: 'Links' }).click();
        await page.locator('span').filter({ hasText: 'Ignore this link' }).getByRole('button').click();
        await expect(page.getByText('1 Ignored')).toBeVisible();
        await expect(page.locator('span').filter({ hasText: '1 Ignored' }).getByRole('img')).toBeVisible();
    });

    consoleTest('Add broke link', async ({testingApi, console, page}) => {
        // Mock check-URL API call
        await page.route('*/**/check-urls*', async route => {
            const json = [{"id":1,"url":"https:\/\/www.google.fr\/","full_url":"https:\/\/www.google.fr\/","status_code":404,"status_type":"broken","ignored":false,"post_id":1,"post_variant_id":1,"post_variant_language_id":1,"post_variant_title":"Test Post"}];
            await route.fulfill({ json });
          });

        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('https://www.google.fr/');
        await page.keyboard.press('Enter');

        await page.getByRole('button', { name: 'Links' }).click();
        await expect(page.getByText('1 Broken')).toBeVisible();
        await expect(page.locator('span').filter({ hasText: '1 Broken' }).locator('path').first()).toBeVisible();
    });
});