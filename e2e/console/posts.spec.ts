import { expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";

test.describe('Paragraph', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await console.visit('');
        await page.getByLabel('Blog Name').click();
        await page.getByLabel('Blog Name').fill('testblog');
        await page.getByRole('button', { name: 'Create Blog' }).click();
        await console.visitAndNav('posts');
        await page.getByRole('button', { name: '+ New' }).click();
    });

    consoleTest('Writing pargraph', async ({testingApi, console, page}) => {
        await page.getByRole('paragraph').click();
        await page.locator('.ProseMirror').fill('Paragraph test');    
        
        await expect(page.locator('.ProseMirror p').first()).toContainText('Paragraph test');
    });

});