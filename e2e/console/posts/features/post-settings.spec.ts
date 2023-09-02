import { expect, test } from "@playwright/test";
import {consoleTest} from "../../consoleTest.ts";

test.describe('Post Settings', () => {

    consoleTest('Has correct data & Discarding', async ({testingApi, console, page}) => {

        const {blog, language} = await testingApi.factory.blogFull({routes: true});

        await testingApi.factory.post({
            attrs: {
                blog_id: blog.id,
                canonical_url: 'https://example.com',
                published_at: Math.floor(new Date("2015-11-25").getTime() / 1000),
                code_head: 'code-head',
                code_foot: 'code-foot'
            },
            variantAttrs: {
                language_id: language.id,
                title: 'Test Post',
                description: 'Test Description',
                slug: 'test-post',
                status: 'published'
            }
        });

        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();

        const settings = await page.getByTestId('post-settings');

        const slugInput = await settings.getByTestId('slug-input');
        await expect(slugInput).toHaveValue('test-post');
        await slugInput.fill('test-post-2');

        const descriptionInput = await settings.getByTestId('description-input');
        await expect(descriptionInput).toHaveValue('Test Description');
        await descriptionInput.fill('Test Description 2');

        const featuredCheckbox = await settings.getByTestId('featured-checkbox');
        await expect(featuredCheckbox.locator('input')).not.toBeChecked();
        await featuredCheckbox.click();

        const publishedTimeInput = await settings.getByTestId('publish-time-input-wrap').locator('input');
        await expect(await publishedTimeInput.inputValue()).toContain('2015-11-25');
        await publishedTimeInput.fill('2015-11-26 01:00:00');

        await page.getByRole('button', { name: 'Discard' }).click();
        await page.getByTestId('discard-popup').getByRole('button', { name: 'Discard' }).click();

        await expect(slugInput).toHaveValue('test-post');
        await expect(descriptionInput).toHaveValue('Test Description');
        await expect(featuredCheckbox).not.toBeChecked();
        await expect(await publishedTimeInput.inputValue()).toContain('2015-11-25');

        // advanced
        await settings.getByRole('button', { name: 'Advanced' }).click();

        const canonicalUrlInput = await settings.getByTestId('canonical-url-input');
        await expect(canonicalUrlInput).toHaveValue('https://example.com');
        await canonicalUrlInput.fill('https://example.com/2');

        const codeHeadInput = await settings.getByTestId('code-head-input');
        await expect(await codeHeadInput.innerText()).toContain('code-head');
        await codeHeadInput.click();
        await page.keyboard.type("-2");
        await page.getByTestId('post-settings').getByRole('img').nth(2).click();

        const codeFootInput = await settings.getByTestId('code-foot-input');
        await expect(await codeFootInput.innerText()).toContain('code-foot');
        await codeFootInput.click();
        await page.keyboard.type("-2");
        await page.getByTestId('post-settings').getByRole('img').nth(3).click();

        await page.getByRole('button', { name: 'Discard' }).click();
        await page.getByTestId('discard-popup').getByRole('button', { name: 'Discard' }).click();

        await expect(canonicalUrlInput).toHaveValue('https://example.com');
        await expect(await codeHeadInput.innerText()).not.toContain('-2');
        await expect(await codeFootInput.innerText()).not.toContain('-2');

    });

});
