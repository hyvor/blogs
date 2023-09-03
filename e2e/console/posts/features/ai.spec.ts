
import { expect, test } from "@playwright/test";
import {consoleTest} from "../../consoleTest.ts";


test.describe('AI', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Open AI pannel', async ({testingApi, console, page}) => {
        await page.getByRole('button', { name: 'AI' }).click();

        await expect(page.getByText('No chat history on this post yet.')).toBeVisible();
    });

    // TODO: Add AI tests when the section will be implemented

});