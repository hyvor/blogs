import { expect, test } from "@playwright/test";
import {consoleTest} from "../consoleTest.ts";


consoleTest('Create post', async ({testingApi, console, page}) => {
    await testingApi.factory.blogFull({routes: true});
    await console.visitAndNav('posts');
    await page.getByTestId('posts').getByText("NEW").click();

    await expect(page.getByPlaceholder('Title...')).toBeVisible();
});

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