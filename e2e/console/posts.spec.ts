import { expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";

test.describe('Paragraph', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.blogFull({routes: true});
        await console.visitAndNav('posts');
        await page.getByRole('button', { name: '+ New' }).click();
    });

    consoleTest('Writing pargraph', async ({testingApi, console, page}) => {
        await page.getByRole('paragraph').click();
        await page.locator('.ProseMirror').fill('Paragraph test');    
        
        await expect(page.locator('.ProseMirror p').first()).toContainText('Paragraph test');
    });

});

test.describe('Heading', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.blogFull({routes: true});
        await console.visitAndNav('posts');
        await page.getByRole('button', { name: '+ New' }).click();
    });

    consoleTest('Writing heading', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');    
        await page.locator('div').filter({ hasText: /^Heading - LargeTo divide main sections of the post$/ }).first().click();
        await page.locator('div').filter({ hasText: /^h2#$/ }).first().fill('Heading test\nh2#');

        await expect(page.getByRole('heading', { name: 'Heading test' })).toBeVisible();
    });

});

test.describe('Image', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.blogFull({routes: true});
        await console.visitAndNav('posts');
        await page.getByRole('button', { name: '+ New' }).click();
    });

    consoleTest('Adding image from URL', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');   
        await page.locator('div').filter({ hasText: /^ImageAdd an image$/ }).first().click();
        await page.getByPlaceholder('Import from URL').fill('https://hyvor.com/img/logo.png');
        await page.getByRole('button', { name: 'Confirm' }).click();

        await expect(page.getByRole('figure', { name: 'Enter a caption...' }).getByRole('img')).toHaveAttribute('src', 'https://hyvor.com/img/logo.png');
    });

    consoleTest('Change image', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');   
        await page.locator('div').filter({ hasText: /^ImageAdd an image$/ }).first().click();
        await page.getByPlaceholder('Import from URL').fill('https://hyvor.com/img/logo.png');
        await page.getByRole('button', { name: 'Confirm' }).click();
        await page.getByRole('button', { name: 'Change Image' }).click();
        await page.getByPlaceholder('Import from URL').fill('https://hyvor.com/img/services/talk.png');
        await page.getByRole('button', { name: 'Confirm' }).click();

        await expect(page.getByRole('figure', { name: 'Enter a caption...' }).getByRole('img')).toHaveAttribute('src', 'https://hyvor.com/img/services/talk.png');
    });

    consoleTest('Modify image caption', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');   
        await page.locator('div').filter({ hasText: /^ImageAdd an image$/ }).first().click();
        await page.getByPlaceholder('Import from URL').fill('https://hyvor.com/img/logo.png');
        await page.getByRole('button', { name: 'Confirm' }).click();
        await page.getByText('Enter a caption...').click();
        await page.locator('div').filter({ hasText: /^Change ImageEnter a caption\.\.\.$/ }).fill('New caption');

        await expect(page.getByRole('figure', { name: 'New caption' })).toBeVisible();
    });

    consoleTest('Deleting image', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');   
        await page.locator('div').filter({ hasText: /^ImageAdd an image$/ }).first().click();
        await page.getByPlaceholder('Import from URL').fill('https://hyvor.com/img/logo.png');
        await page.getByRole('button', { name: 'Confirm' }).click();

        await page.getByRole('figure', { name: 'Enter a caption...' }).getByRole('img').click();
        await page.keyboard.press('Backspace');

        await expect(page.locator('.ProseMirror').first()).not.toContainText('Enter a caption...');
    });
});