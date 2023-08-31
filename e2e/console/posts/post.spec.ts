import { expect, test } from "@playwright/test";
import {consoleTest} from "../consoleTest.ts";


consoleTest('Create post', async ({testingApi, console, page}) => {
    await testingApi.factory.blogFull({routes: true});
    await console.visitAndNav('posts');
    await page.getByTestId('posts').getByText("NEW").click();

    await expect(page.getByPlaceholder('Title...')).toBeVisible();
});

test.describe('multi language', () => {

    consoleTest('Create language variant', async({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({
            routes: true,
            languageAttrs: {code: 'en', name: 'English'}
        });
        await testingApi.factory.language({blog_id: blog.id, code: 'fr', name: 'French'});
        
        await testingApi.factory.post({
            attrs: {
                blog_id: blog.id,
            },
            variantAttrs: {
                language_id: language.id,
                title: 'Test Post',
                status: 'draft'
            }
        });
    
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
    
        const enTag = await page.getByTestId('lang-tag-en');
        const frTag = await page.getByTestId('lang-tag-fr');
    
        await expect(enTag).toHaveClass(/\bactive\b/);
        await enTag.hover();
        await expect(page.getByText('English - Draft')).toBeVisible();
       
        await frTag.click();
    
        await expect(frTag).toHaveClass(/\bactive\b/);
        await frTag.hover();
        await expect(page.getByText('French - Draft')).toBeVisible();
    
    });

    consoleTest('RTL', async({testingApi, console, page}) => {

        const {blog, language} = await testingApi.factory.blogFull({
            routes: true,
            languageAttrs: {code: 'ar', name: 'Arabic', direction: 'rtl'}
        });
    
        await testingApi.factory.post({
            attrs: {blog_id: blog.id},
            variantAttrs: {language_id: language.id, status: 'draft', title: 'Test Post'}
        });
    
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
    
        const arTag = await page.getByTestId('lang-tag-ar');
        await expect(arTag).toHaveClass(/\bactive\b/);
        await arTag.hover();
        await expect(page.getByText('Arabic - Draft')).toBeVisible();

        const postEditorWrap = await page.locator('.post-editor-wrap');
        await expect(await postEditorWrap.getAttribute('dir')).toBe('rtl');

    });

});


test.describe('Preview', () => {

    consoleTest('Preview non published', async ({testingApi, console, page}) => {

        await testingApi.factory.testPost();

        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        const previewButton = await page.getByTestId('preview-button-link');
        const href = await previewButton.getAttribute('href');

        await expect(href).toContain('/p/');

    });

    consoleTest('preview published', async ({testingApi, console, page}) => {

        await testingApi.factory.testPost({postVariantAttrs: {status: 'published'}});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();

        const previewButton = await page.getByTestId('preview-button-link');
        const href = await previewButton.getAttribute('href');

        await expect(href).toBeNull();
        await previewButton.click();

        await expect(page.getByText('Published Post')).toBeVisible();

    });

});


consoleTest('Publish post', async ({testingApi, console, page}) => {

    const {blog, language} = await testingApi.factory.blogFull({routes: true});

    await testingApi.factory.post({
        attrs: {
            blog_id: blog.id,
        },
        variantAttrs: {
            language_id: language.id,
            title: 'Test Post',
            status: 'draft'
        }
    });

    await console.visitAndNav('posts');
    await page.getByRole('link', { name: 'Test Post' }).click();

    await page.getByRole('button', { name: 'Publish' }).click();

});

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
