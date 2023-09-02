import { expect, test } from "@playwright/test";
import { consoleTest } from "../../consoleTest";

consoleTest('AI Translating', async ({ testingApi, console, page }) => {

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

    await page.locator('.ProseMirror').fill('Hello World');

    const enTag = await page.getByTestId('lang-tag-en');
    const frTag = await page.getByTestId('lang-tag-fr');

    frTag.click();

    expect(await page.locator('.ProseMirror')).toBe(1);

});