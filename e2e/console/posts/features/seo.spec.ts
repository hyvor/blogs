
import { expect, test } from "@playwright/test";
import {consoleTest} from "../../consoleTest.ts";


test.describe('SEO', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Add primary keyword', async ({testingApi, console, page}) => {
        await page.getByText('SEO').click();
        await page.locator('div').filter({ hasText: /^Primary Keyword\+ Add$/ }).getByRole('button', { name: '+ Add' }).click();
        await page.getByTestId('post-seo').getByRole('textbox').fill('post');
        await page.getByTestId('post-seo').locator('a').first().click();
        await page.reload();

        await page.getByText('SEO').click();
        await expect(page.getByText('post', { exact: true })).toBeVisible();
    });

    consoleTest('Add secondary keyword', async ({testingApi, console, page}) => {
        await page.getByText('SEO').click();
        await page.locator('div').filter({ hasText: /^Secondary Keywords\+ Add$/ }).getByRole('button', { name: '+ Add' }).click();
        await page.getByTestId('post-seo').getByRole('textbox').fill('post');
        await page.getByTestId('post-seo').locator('a').first().click();

        await page.reload();
        await expect(page.getByText('post', { exact: true })).toBeVisible();
    });
});