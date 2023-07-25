import { expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";

test.describe('Paragraph', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Writing pargraph', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('Paragraph test');    
        
        await expect(page.locator('.ProseMirror p').first()).toContainText('Paragraph test');
    });

    consoleTest('Editing pargraph', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('Paragraph test');    
        await page.locator('.ProseMirror').fill('Paragraph edited');
        
        await expect(page.locator('.ProseMirror p').first()).toContainText('Paragraph edited');
    });

    consoleTest('Deleting pargraph', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('Paragraph test');
        await page.locator('.ProseMirror').fill('');
        
        await expect(page.locator('.ProseMirror').first()).not.toContainText('Paragraph test');
    });
});

test.describe('Heading', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Writing heading 2', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');    
        await page.locator('div').filter({ hasText: /^Heading - LargeTo divide main sections of the post$/ }).first().click();
        await page.locator('div').filter({ hasText: /^h2#$/ }).first().fill('Heading test\nh2#');

        await expect(page.getByRole('heading', { name: 'Heading test' })).toBeVisible();
    });

    consoleTest('Writing heading 3', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');    
        await page.locator('div').filter({ hasText: /^Heading - MediumTo divide small sections of the post$/ }).first().first().click();
        await page.locator('div').filter({ hasText: /^h3#$/ }).first().fill('Heading test\nh3#');

        await expect(page.getByRole('heading', { name: 'Heading test' })).toBeVisible();
    });

});

test.describe('Image', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding image from URL', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');   
        await page.locator('div').filter({ hasText: /^ImageAdd an image$/ }).first().click();
        await page.getByPlaceholder('Import from URL').fill('https://hyvor.com/img/logo.png');
        await page.getByRole('button', { name: 'Confirm' }).click();

        await expect(page.getByRole('figure', { name: 'Enter a caption...' }).getByRole('img')).toHaveAttribute('src', 'https://hyvor.com/img/logo.png');
    });

    /*TODO: Add an image from a file*/

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

test.describe('Quote', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding quote', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^QuoteCapture a quote$/ }).first().click();
        await page.locator('.ProseMirror').fill('This is a quote');

        await expect(page.locator('.ProseMirror blockquote').first()).toContainText('This is a quote');
    });

    consoleTest('Deleting quote', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^QuoteCapture a quote$/ }).first().click();
        await page.locator('.ProseMirror').fill('This is a quote');
        await page.getByText('This is a quote').click();
        await page.locator('div').filter({ hasText: /^This is a quote$/ }).fill('');
        await page.keyboard.press('Backspace');

        await expect(page.locator('.ProseMirror').first()).not.toContainText('This is a quote');
    });
});

test.describe('Callout', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding callout', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^CalloutWrite something standing out$/ }).first().click();
        await page.locator('div').filter({ hasText: /^💡$/ }).fill('This is a callout');

        await expect(page.getByText('This is a callout')).toBeVisible();
    });

    consoleTest('Changing callout background color', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^CalloutWrite something standing out$/ }).first().click();
        await page.locator('div').filter({ hasText: /^💡$/ }).fill('This is a callout');

        await page.getByRole('complementary').locator('span').nth(1).click();
        await page.getByTitle('#eedfda').click();
        await page.locator('.color-picker-view > div').first().click();

       await expect(page.locator('aside').filter({ hasText: /^💡This is a callout$/ })).toHaveAttribute('style', 'background-color: rgb(238, 223, 218); color: rgb(0, 0, 0);');
    });

    consoleTest('Changing callout text color', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^CalloutWrite something standing out$/ }).first().click();
        await page.locator('div').filter({ hasText: /^💡$/ }).fill('This is a callout');

        await page.getByRole('complementary').locator('div').nth(1).click();
        await page.getByTitle('#fff').click();
        await page.locator('.color-picker-view > div').first().click();

       await expect(page.locator('aside').filter({ hasText: /^💡This is a callout$/ })).toHaveAttribute('style', 'background-color: rgb(241, 241, 239); color: rgb(255, 255, 255);');
    });

});

test.describe('Code block', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding a code block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Code BlockA block of code$/ }).first().click();
        await page.locator('.code-toolbar-inputs > input').first().fill('python');
        await page.locator('#middle pre').nth(1).click();
        await page.locator('textarea').nth(2).fill('def function:');

        await expect(page.locator('.code-toolbar-inputs > input').first()).toHaveValue('python');
        await expect(page.locator('span').filter({  hasText: /^def function:$/ })).toBeVisible();
    });

    consoleTest('Editing a code block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Code BlockA block of code$/ }).first().click();
        await page.locator('.code-toolbar-inputs > input').first().fill('python');
        await page.locator('#middle pre').nth(1).click();
        await page.locator('textarea').nth(2).fill('def function:');
        await page.locator('textarea').nth(2).fill('def foo:');
        
        await expect(page.locator('.code-toolbar-inputs > input').first()).toHaveValue('python');
        await expect(page.locator('span').filter({  hasText: /^def foo:$/ })).toBeVisible();
    });

    consoleTest('Deleting a code block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Code BlockA block of code$/ }).first().click();
        await page.locator('.code-toolbar-inputs > input').first().fill('python');
        await page.locator('textarea').nth(2).fill('');
        await page.keyboard.press('Backspace');

        await expect(page.locator('.ProseMirror').first()).not.toContainText('def function:');
    });
});

test.describe('Custom HTML', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding a custom HTML block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Custom HTML\/TwigAdd custom HTML \(or Twig\)$/ }).first().click();
        await page.getByRole('textbox').nth(4).fill('<div>');

        await expect(page.locator('pre').filter({  hasText: /^<div>$/ })).toBeVisible();
    });

    consoleTest('Editing a custom HTML block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Custom HTML\/TwigAdd custom HTML \(or Twig\)$/ }).first().click();
        await page.getByRole('textbox').nth(4).fill('<div>');
        await page.getByRole('textbox').nth(4).fill('<span>');

        await expect(page.locator('pre').filter({  hasText: /^<span>$/ })).toBeVisible();
    });

    consoleTest('Deleting a custom HTML block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Custom HTML\/TwigAdd custom HTML \(or Twig\)$/ }).first().click();
        
        await page.getByRole('textbox').nth(4).fill('');
        await page.keyboard.press('Backspace');

        await expect(page.locator('.ProseMirror').first()).not.toContainText('<div>');
    });
});

test.describe('Lists', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding bullet list nodes', async ({testingApi, console, page}) => {
        await page.getByRole('paragraph').click();
        await page.locator('.ProseMirror').fill('-');
        await page.keyboard.press('Space');
        await page.locator('.ProseMirror').fill('Bullet 1');
        await page.keyboard.press('Enter');
        await page.getByRole('paragraph').nth(1).fill('Bullet 2');

        await expect(page.locator('.ProseMirror ul').first()).toBeVisible();
        await expect(page.locator('.ProseMirror li').first()).toContainText('Bullet 1');
        await expect(page.locator('.ProseMirror li').nth(1)).toContainText('Bullet 2');
    });

    consoleTest('Adding ordered list nodes', async ({testingApi, console, page}) => {
        await page.getByRole('paragraph').click();
        await page.locator('.ProseMirror').fill('1.');
        await page.keyboard.press('Space');
        await page.locator('.ProseMirror').fill('Item 1');
        await page.keyboard.press('Enter');
        await page.getByRole('paragraph').nth(1).fill('Item 2');

        await expect(page.locator('.ProseMirror ol').first()).toBeVisible();
        await expect(page.locator('.ProseMirror li').first()).toContainText('Item 1');
        await expect(page.locator('.ProseMirror li').nth(1)).toContainText('Item 2');
    });

    consoleTest('Editing bullet list nodes', async ({testingApi, console, page}) => {
        await page.getByRole('paragraph').click();
        await page.locator('.ProseMirror').fill('-');
        await page.keyboard.press('Space');
        await page.locator('.ProseMirror').fill('Bullet 1');
        await page.keyboard.press('Enter');
        await page.getByRole('paragraph').nth(1).fill('Bullet 2');
        await page.getByRole('paragraph').nth(1).fill('Bullet 2.5');

        await expect(page.locator('.ProseMirror ul').first()).toBeVisible();
        await expect(page.locator('.ProseMirror li').first()).toContainText('Bullet 1');
        await expect(page.locator('.ProseMirror li').nth(1)).toContainText('Bullet 2.5');
    });

    consoleTest('Delete bullet list nodes', async ({testingApi, console, page}) => {
        await page.getByRole('paragraph').click();
        await page.locator('.ProseMirror').fill('-');
        await page.keyboard.press('Space');
        await page.locator('.ProseMirror').fill('Bullet 1');
        await page.keyboard.press('Enter');
        await page.getByRole('paragraph').nth(1).fill('Bullet 2');
        await page.getByRole('paragraph').nth(1).fill('');
        await page.keyboard.press('Backspace');
        await page.keyboard.press('Backspace');
        await page.getByRole('paragraph').first().fill('');
        await page.keyboard.press('Backspace');

        await expect(page.locator('.ProseMirror').first()).not.toContainText('Bullet 1');
        await expect(page.locator('.ProseMirror').first()).not.toContainText('Bullet 2');
    });

});

test.describe('Divider', () => {
    
    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding divider between paragraphs', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Before divider');
        await page.locator('.ProseMirror').press('Enter');
        await page.getByRole('paragraph').nth(1).fill('/');
        await page.locator('div').filter({ hasText: /^DividerDivide sections with a horizontal line$/ }).first().click();
        await page.keyboard.press('Enter');
        await page.getByRole('paragraph').nth(1).fill('After divider');

        await expect(page.getByText('Before divider')).toBeVisible();
        await expect(page.locator('.ProseMirror hr').first()).toBeVisible();
        await expect(page.getByText('After divider')).toBeVisible();
    });

    consoleTest('Deleting divider between paragraphs', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Before divider');
        await page.locator('.ProseMirror').press('Enter');
        await page.getByRole('paragraph').nth(1).fill('/');
        await page.locator('div').filter({ hasText: /^DividerDivide sections with a horizontal line$/ }).first().click();
        await page.keyboard.press('Enter');
        await page.getByRole('paragraph').nth(1).fill('After divider');
        await page.getByRole('paragraph').nth(1).fill('');
        await page.keyboard.press('Backspace');
        await page.keyboard.press('Backspace');

        await expect(page.getByText('Before divider')).toBeVisible();
        await expect(page.locator('.ProseMirror hr').first()).not.toBeVisible();
    });

});

test.describe('Highlighting (Bold, italic, code, ...)', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Turning text to bold', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Bold');
        // Select the text
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(2)').first().click();
        await page.locator('.ProseMirror').click();

        await expect(page.locator('.ProseMirror strong').first()).toContainText('Bold');
    });

    consoleTest('Turning text to itatlic', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Italic');
        // Select the text
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(3)').first().click();
        await page.locator('.ProseMirror').click();

        await expect(page.locator('.ProseMirror em').first()).toContainText('Italic');
    });

    consoleTest('Turning text to bold and itatlic', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Boldlic');
        // Select the text
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(2)').first().click();
        await page.locator('.pm-tooltip > span:nth-child(3)').first().click();
        await page.locator('.ProseMirror').click();

        await expect(page.locator('.ProseMirror em').first()).toContainText('Boldlic');
        await expect(page.locator('.ProseMirror strong').first()).toContainText('Boldlic');
    });

    consoleTest('Turning text to code', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Code');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(4)').first().click();
        await page.locator('.ProseMirror').click();

        await expect(page.locator('.ProseMirror code').first()).toContainText('Code');
    });

    consoleTest('Add overline on text', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(5)').first().click();
        await page.locator('.ProseMirror').click();

        await expect(page.locator('.ProseMirror s').first()).toContainText('Over');
    });

    consoleTest('Turn text into link', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('google.fr');
        await page.keyboard.press('Enter');

        await expect(page.locator('.ProseMirror a').first()).toHaveAttribute('href', 'google.fr');
    });

    consoleTest('Turn text into link and modify it', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('google.fr');
        await page.keyboard.press('Enter');
        await page.locator('.ProseMirror a').first().click();
        await page.locator('.pm-link-tooltip > div > button').first().click();
        await page.locator('input').nth(8).fill('google.com');
        await page.keyboard.press('Enter');

        await expect(page.locator('.ProseMirror a').first()).toHaveAttribute('href', 'google.com');
    });

    consoleTest('Turn text into link and remove the link', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('google.fr');
        await page.keyboard.press('Enter');
        await page.locator('.ProseMirror a').first().click();
        await page.locator('.pm-link-tooltip > div > button').nth(1).click();

        await expect(page.locator('.ProseMirror a').first()).not.toBeVisible();
    });
});

test.describe('Bookmark', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding a bookmark', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Link BookmarkLink preview as a bookmark$/ }).first().click();
        await page.getByPlaceholder('Paste URL here to generate a bookmark').fill('https://google.com');
        await page.keyboard.press('Enter');

        await expect(page.locator('#middle').getByText('Search the world\'s information, including webpages, images, videos and more. Goo')).toBeVisible();
    });

    consoleTest('Deleting a bookmark', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Link BookmarkLink preview as a bookmark$/ }).first().click();
        await page.getByPlaceholder('Paste URL here to generate a bookmark').fill('https://google.com');
        await page.keyboard.press('Enter');
        await page.getByText('Google Search the world\'s information, including webpages, images, videos and mo').click();
        await page.keyboard.press('Backspace');

        await expect(page.locator('#middle').getByText('Search the world\'s information, including webpages, images, videos and more. Goo')).not.toBeVisible();
    });
});

test.describe('Embed', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding a embed', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^EmbedEmbed content from 1500\+ platforms$/ }).first().click();
        await page.getByPlaceholder('Paste URL to embed (Youtube, Twitter, and 1000+ platforms supported)').fill('https://www.youtube.com/watch?v=bK6ldnjE3Y0');
        await page.getByPlaceholder('Paste URL to embed (Youtube, Twitter, and 1000+ platforms supported)').press('Enter');

        await expect(page.locator('iframe').first()).toHaveAttribute('src', 'https://www.youtube.com/embed/bK6ldnjE3Y0?rel=0');
    });

    consoleTest('Delete a embed', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^EmbedEmbed content from 1500\+ platforms$/ }).first().click();
        await page.getByPlaceholder('Paste URL to embed (Youtube, Twitter, and 1000+ platforms supported)').fill('https://www.youtube.com/watch?v=bK6ldnjE3Y0');
        await page.getByPlaceholder('Paste URL to embed (Youtube, Twitter, and 1000+ platforms supported)').press('Enter');
        await page.locator('figcaption').click();
        await page.keyboard.press('Backspace');

        await expect(page.locator('iframe').first()).not.toBeVisible();
    });

    consoleTest('Delete an empty embed', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^EmbedEmbed content from 1500\+ platforms$/ }).first().click();
        await page.keyboard.press('Backspace');

        await expect(page.locator('iframe').first()).not.toBeVisible();
    });
});

test.describe('Table', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        const {blog, language} = await testingApi.factory.blogFull({routes: true});
        await testingApi.factory.post({blog_id: blog.id, language_id: language.id, title: 'Test Post'});
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Create and a fill a table', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('tr:nth-child(2) > td > p').first().fill('cell2');

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).toContainText('cell1');
        await expect(page.locator('td').nth(3)).toContainText('cell2');
    });

    consoleTest('Create and add a row', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('tr:nth-child(2) > td > p').first().fill('cell2');
        await page.getByRole('button', { name: '+' }).nth(1).click();

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).toContainText('cell1');
        await expect(page.locator('td').nth(3)).toContainText('cell2');
        await expect(page.locator('td').nth(9)).toContainText('');
    });

    consoleTest('Create and add a column', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('tr:nth-child(2) > td > p').first().fill('cell2');
        await page.locator('div').filter({ hasText: /^cell1cell2\+$/ }).getByRole('button', { name: '+' }).click();

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).toContainText('cell1');
        await expect(page.locator('td').nth(4)).toContainText('cell2');
        await expect(page.locator('td').nth(3)).toContainText('');
    });

    consoleTest('Create and delete table', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('tr:nth-child(2) > td > p').first().fill('cell2');
        await page.locator('#delete-table-button').click();

        await expect(page.locator('table').first()).not.toBeVisible();
    });
});