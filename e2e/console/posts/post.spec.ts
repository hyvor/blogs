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


test.describe('Post Status', () => {

    consoleTest('Publish post', async ({testingApi, console, page}) => {

        const {blog, language} = await testingApi.factory.blogFull({routes: true});
    
        await testingApi.factory.post({
            attrs: {
                blog_id: blog.id,
            },
            variantAttrs: {
                language_id: language.id,
                title: 'Test Post',
                status: 'draft',
                slug: null,
                description: null
            }
        });
    
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.getByRole('button', { name: 'Publish' }).click();

        // validations
        await expect(page.getByText('Slug is not set, it will be auto-generated')).toBeVisible();
        await expect(page.getByText('Description is not set')).toBeVisible();

        // publish
        await page.getByTestId('publish-popup').getByRole('button', { name: 'Publish' }).click();
        await expect(page.getByText('Post Published')).toBeVisible();

        const href = await page.getByTestId('publish-popup-view-link').getAttribute('href');
        await expect(href).toContain('/test-post');

        await expect(page.getByTestId('post-status')).toHaveText('published');
        
    });

    consoleTest('Unpublish post', async ({console, page}) => {
       
        await console.visitNewPost({postVariantAttrs: {status: 'published'}});
        await page.getByRole('button', { name: 'Unpublish' }).click();

        await page.getByTestId('unpublish-popup').getByRole('button', { name: 'Unpublish' }).click();
        await expect(page.getByText('Post unpublished successfully')).toBeVisible();

        await expect(page.getByTestId('post-status')).toHaveText('draft');

    });

    consoleTest('Schedule Post', async ({testingApi, console, page}) => {

        await testingApi.factory.testPost({postVariantAttrs: {status: 'draft'}});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();

        await page.getByRole('button', { name: 'Publish' }).click();
        await page.getByText('Schedule for Later').click();

        const publishTimeInput = await page.getByTestId('publish-popup').locator('input[type="text"]');
        await publishTimeInput.fill('2030-01-01 01:00:00');

        await page.getByTestId('publish-popup').getByRole('button', { name: 'Schedule' }).click();
        await expect(page.getByText('Post Scheduled')).toBeVisible();

        const settings = await page.getByTestId('post-settings');
        const publishedTimeInput = await settings.getByTestId('publish-time-input-wrap').locator('input');
        await expect(await publishedTimeInput.inputValue()).toContain('2030-01-01');

        await expect(page.getByTestId('post-status')).toHaveText('scheduled');

    });

    consoleTest('Unschedule Post', async ({console, page}) => {

        await console.visitNewPost({postVariantAttrs: {status: 'scheduled'}});
        await page.getByRole('button', { name: 'Unschedule' }).click();

        await page.getByTestId('unpublish-popup').getByRole('button', { name: 'Unschedule' }).click();
        await expect(page.getByText('Post unscheduled successfully')).toBeVisible();

        await expect(page.getByTestId('post-status')).toHaveText('draft');
    });

});
