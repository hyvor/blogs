import { expect, test } from "@playwright/test";
import {consoleTest} from "../../consoleTest.ts";

test.describe('Links', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Add a link', async ({testingApi, console, page}) => {
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
    });

    consoleTest('Refresh a link', async ({testingApi, console, page}) => {
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
    });
});