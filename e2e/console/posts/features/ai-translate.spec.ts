import { expect, test } from "@playwright/test";
import { consoleTest } from "../../consoleTest.ts";
import { pmc } from "../../../helpers/prosemirror-test-helper.ts";

consoleTest('AI Translating', async ({ testingApi, console, page }) => {

    await page.route('**/ai/translate', async route => {

        await route.fulfill({
            json: {
                title: 'Post de test',
                description: '',
                slug: 'post-de-test',
                content: pmc.p('Bonjour le monde'),
            }
        })

    });

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

    await frTag.click();
    await page.getByText('Auto-Translate').click();
    await page.getByTestId('auto-translate-popup').getByText('Auto-Translate').click();

});