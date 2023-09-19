
import { expect, test } from "@playwright/test";
import {consoleTest} from "../../consoleTest.ts";


test.describe('AI', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Load empty history', async ({testingApi, console, page}) => {
        await page.getByRole('button', { name: 'AI' }).click();

        await expect(page.getByText('No chat history on this post yet.')).toBeVisible();
    });

    consoleTest('Load filled history', async ({testingApi, console, page}) => {
        // Mock history API call
        await page.route('*/**/post-history*', async route => {
            const json = [{"id": "1","created_at": "1694879520","post_id": "1","prompt": "Write \"post\"","gpt_response": "## GPT history"}];
            await route.fulfill({ json });
          });


        await page.getByRole('button', { name: 'AI' }).click(); 
        await expect(page.getByText('GPT history')).toBeVisible();
    });

    consoleTest('Generate content with AI', async ({testingApi, console, page}) => {
        // Mock history API call
        await page.route('*/**/post-history*', async route => {
            const json = [{"id": "1","created_at": "1694879520","post_id": "1","prompt": "Write \"post\"","gpt_response": "## GPT history"}];
            await route.fulfill({ json });
          });
          // Mock prompt API call
          await page.route('*/**/prompt*', async route => {
            const json = {"id": "1","created_at": "1694889820","post_id": "1","prompt": "Write \"post\"","gpt_response": "## GPT live"};
            await route.fulfill({ json });
          });

        await page.getByRole('button', { name: 'AI' }).click(); 
        await page.getByRole('button', { name: 'Generate' }).click();
        await expect(page.getByText('GPT live')).toBeVisible();
    });

    consoleTest('Add to generated content to post', async ({testingApi, console, page}) => {
        // Mock history API call
        await page.route('*/**/post-history*', async route => {
            const json = [{"id": "1","created_at": "1694879520","post_id": "1","prompt": "Write \"post\"","gpt_response": "## GPT history"}];
            await route.fulfill({ json });
          });
          // Mock prompt API call
          await page.route('*/**/prompt*', async route => {
            const json = {"id": "1","created_at": "1694889820","post_id": "1","prompt": "Write \"post\"","gpt_response": "## GPT live"};
            await route.fulfill({ json });
          });

        await page.getByRole('button', { name: 'AI' }).click(); 
        await page.getByRole('button', { name: 'Generate' }).click();
        await page.getByRole('button', { name: 'Add to Editor' }).nth(1).click();
        await expect(page.getByText('GPT live').first()).toBeVisible();
    });

});