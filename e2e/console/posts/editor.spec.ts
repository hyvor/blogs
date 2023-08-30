import { expect, test } from "@playwright/test";
import {consoleTest} from "../consoleTest.ts";

test.describe('Paragraph', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
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
        await testingApi.factory.testPost();
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
        await testingApi.factory.testPost();

        const imageUrl = 'https://cdn.shopify.com/app-store/listing_images/8e6dbed9da2f06095141afa9e25fc33c/icon/CJWKsfjt3_kCEAE=.png';

        await page.goto(imageUrl);

        await page.click('img');

        await page.keyboard.down('Control');
        await page.keyboard.press('KeyC');
        await page.keyboard.up('Control');

        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();

        await page.locator('.ProseMirror').fill(''); 
        await page.locator('.ProseMirror').focus();
        await page.keyboard.down('Control');
        await page.keyboard.press('KeyV');
        await page.keyboard.up('Control');
    });


    consoleTest('Adding image', async ({testingApi, console, page}) => {
        await expect(page.locator('figcaption')).toBeVisible();
    });

    consoleTest('Adding caption', async ({testingApi, console, page}) => {
        await page.locator('figcaption').click();
        await page.locator('div').filter({ hasText: /^Change$/ }).first().fill('\n\n\nChange\nTest');        
    });
});

test.describe('Quote', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
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
        await testingApi.factory.testPost();
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

       await expect(page.locator('aside').filter({ hasText: /^💡This is a callout$/ })).toHaveAttribute('style', 'background-color: rgb(238, 223, 218); color: rgb(0, 0, 0);');
    });

    consoleTest('Changing callout text color', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^CalloutWrite something standing out$/ }).first().click();
        await page.locator('div').filter({ hasText: /^💡$/ }).fill('This is a callout');

        await page.getByRole('complementary').locator('div').nth(1).click();
        await page.getByTitle('#fff').click();

       await expect(page.locator('aside').filter({ hasText: /^💡This is a callout$/ })).toHaveAttribute('style', 'background-color: rgb(241, 241, 239); color: rgb(255, 255, 255);');
    });

});

test.describe('Code block', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding a code block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Code BlockA block of code$/ }).first().click();
        await page.locator('.code-toolbar-inputs > input').first().fill('python');
        await page.locator('#middle pre').nth(1).click();
        await page.locator('textarea').nth(1).fill('def function:');

        await expect(page.locator('.code-toolbar-inputs > input').first()).toHaveValue('python');
        await expect(page.locator('span').filter({  hasText: /^def function:$/ })).toBeVisible();
    });

    consoleTest('Editing a code block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Code BlockA block of code$/ }).first().click();
        await page.locator('.code-toolbar-inputs > input').first().fill('python');
        await page.locator('#middle pre').nth(1).click();
        await page.locator('textarea').nth(1).fill('def function:');
        await page.locator('textarea').nth(1).fill('def foo:');
        
        await expect(page.locator('.code-toolbar-inputs > input').first()).toHaveValue('python');
        await expect(page.locator('span').filter({  hasText: /^def foo:$/ })).toBeVisible();
    });

    consoleTest('Deleting a code block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Code BlockA block of code$/ }).first().click();
        await page.locator('.code-toolbar-inputs > input').first().fill('python');
        await page.locator('textarea').nth(1).fill('');
        await page.keyboard.press('Backspace');

        await expect(page.locator('.ProseMirror').first()).not.toContainText('def function:');
    });
});

test.describe('Custom HTML', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding a custom HTML block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Custom HTML\/TwigAdd custom HTML \(or Twig\)$/ }).first().click();
        await page.locator('textarea').nth(1).fill('<div>');

        await expect(page.locator('pre').filter({  hasText: /^<div>$/ })).toBeVisible();
    });

    consoleTest('Editing a custom HTML block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Custom HTML\/TwigAdd custom HTML \(or Twig\)$/ }).first().click();
        await page.locator('textarea').nth(1).fill('<div>');
        await page.locator('textarea').nth(1).fill('<span>');

        await expect(page.locator('pre').filter({  hasText: /^<span>$/ })).toBeVisible();
    });

    consoleTest('Deleting a custom HTML block', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Custom HTML\/TwigAdd custom HTML \(or Twig\)$/ }).first().click();
        
        await page.locator('textarea').nth(1).fill('');
        await page.keyboard.press('Backspace');

        await expect(page.locator('.ProseMirror').first()).not.toContainText('<div>');
    });
});

test.describe('Lists', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
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
        await testingApi.factory.testPost();
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
        await testingApi.factory.testPost();
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
        await page.getByTestId('posts').locator('input[type="text"]').fill('google.com');
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
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding a bookmark', async ({testingApi, console, page}) => {
        await page.route('*/**/url-data*', async route => {
            const json = {"url":"https:\/\/www.google.com\/","original_url":"https:\/\/www.google.com\/","domain":"www.google.com","html":null,"title":"Google","description":"Search the world's information, including webpages, images, videos and more. Google has many special features to help you find exactly what you're looking for.","thumbnail_url":null,"icon_url":"https:\/\/www.google.com\/favicon.ico","site":"Google"};
            await route.fulfill({ json });
          });
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^Link BookmarkLink preview as a bookmark$/ }).first().click();
        await page.getByPlaceholder('Paste URL here to generate a bookmark').fill('https://google.com');
        await page.keyboard.press('Enter');

        await expect(page.locator('#middle').getByText('Search the world\'s information, including webpages, images, videos and more. Goo')).toBeVisible();
    });

    consoleTest('Deleting a bookmark', async ({testingApi, console, page}) => {
        await page.route('*/**/url-data*', async route => {
            const json = {"url":"https:\/\/www.google.com\/","original_url":"https:\/\/www.google.com\/","domain":"www.google.com","html":null,"title":"Google","description":"Search the world's information, including webpages, images, videos and more. Google has many special features to help you find exactly what you're looking for.","thumbnail_url":null,"icon_url":"https:\/\/www.google.com\/favicon.ico","site":"Google"};
            await route.fulfill({ json });
          });
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
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Adding a embed', async ({testingApi, console, page}) => {
        await page.route('*/**/url-data*', async route => {
            const json = {"url":"https:\/\/www.youtube.com\/watch?v=uYPbbksJxIg","original_url":"https:\/\/www.youtube.com\/watch?v=uYPbbksJxIg","domain":"www.youtube.com","html":"<div style=\"left: 0; width: 100%; height: 0; position: relative; padding-bottom: 56.25%;\"><iframe src=\"https:\/\/www.youtube.com\/embed\/uYPbbksJxIg?rel=0\" style=\"top: 0; left: 0; width: 100%; height: 100%; position: absolute; border: 0;\" allowfullscreen scrolling=\"no\" allow=\"accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share;\"><\/iframe><\/div>","title":"Oppenheimer | New Trailer","description":"Oppenheimer - In Theaters 7 21 23\n\nWritten and directed by Christopher Nolan, Oppenheimer is an IMAX\u00ae-shot epic thriller that thrusts audiences into the pulse-pounding paradox of the enigmatic man who must risk destroying the world in order to save it. \n ","thumbnail_url":"https:\/\/i.ytimg.com\/vi\/uYPbbksJxIg\/maxresdefault.jpg","icon_url":"https:\/\/www.youtube.com\/s\/desktop\/0529f0d5\/img\/favicon_144x144.png","site":"YouTube"};
            await route.fulfill({ json });
          });

        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^EmbedEmbed content from 1500\+ platforms$/ }).first().click();
        await page.getByPlaceholder('Paste URL to embed (Youtube, Twitter, and 1000+ platforms supported)').fill('https://www.youtube.com/watch?v=uYPbbksJxIg');
        await page.getByPlaceholder('Paste URL to embed (Youtube, Twitter, and 1000+ platforms supported)').press('Enter');

        await expect(page.locator('x-embed').first()).toHaveAttribute('data-url', 'https://www.youtube.com/watch?v=uYPbbksJxIg');
    });

    consoleTest('Delete a embed', async ({testingApi, console, page}) => {
        await page.route('*/**/url-data*', async route => {
            const json = {"url":"https:\/\/www.youtube.com\/watch?v=uYPbbksJxIg","original_url":"https:\/\/www.youtube.com\/watch?v=uYPbbksJxIg","domain":"www.youtube.com","html":"<div style=\"left: 0; width: 100%; height: 0; position: relative; padding-bottom: 56.25%;\"><iframe src=\"https:\/\/www.youtube.com\/embed\/uYPbbksJxIg?rel=0\" style=\"top: 0; left: 0; width: 100%; height: 100%; position: absolute; border: 0;\" allowfullscreen scrolling=\"no\" allow=\"accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share;\"><\/iframe><\/div>","title":"Oppenheimer | New Trailer","description":"Oppenheimer - In Theaters 7 21 23\n\nWritten and directed by Christopher Nolan, Oppenheimer is an IMAX\u00ae-shot epic thriller that thrusts audiences into the pulse-pounding paradox of the enigmatic man who must risk destroying the world in order to save it. \n ","thumbnail_url":"https:\/\/i.ytimg.com\/vi\/uYPbbksJxIg\/maxresdefault.jpg","icon_url":"https:\/\/www.youtube.com\/s\/desktop\/0529f0d5\/img\/favicon_144x144.png","site":"YouTube"};
            await route.fulfill({ json });
          });

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
        await testingApi.factory.testPost();
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

    consoleTest('Create and add a column before', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(1).fill('cell2');
        await page.getByText('cell2').click();
        await page.locator('.toggle-table-menu-button').first().click();
        await page.getByRole('button', { name: 'Insert Before' }).click();
        await page.getByRole('row', { name: 'cell1 cell2' }).getByRole('paragraph').nth(1).click();
        await page.locator('td').nth(1).fill('cell1.5');

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).toContainText('cell1');
        await expect(page.locator('td').nth(1)).toContainText('cell1.5');
        await expect(page.locator('td').nth(2)).toContainText('cell2');
    });

    consoleTest('Create and add a column after', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(1).fill('cell2');
        await page.getByText('cell1').click();
        await page.locator('.toggle-table-menu-button').first().click();
        await page.getByRole('button', { name: 'Insert After' }).click();
        await page.getByRole('row', { name: 'cell1 cell2' }).getByRole('paragraph').nth(1).click();
        await page.locator('td').nth(1).fill('cell1.5');

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).toContainText('cell1');
        await expect(page.locator('td').nth(1)).toContainText('cell1.5');
        await expect(page.locator('td').nth(2)).toContainText('cell2');
    });

    consoleTest('Create and add a row above', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(3).fill('cell2');
        await page.getByText('cell2').click();
        await page.locator('div').filter({ hasText: /^cell1cell2\+$/ }).getByRole('button').first().click();
        await page.getByRole('button', { name: 'Insert Above' }).click();
        await page.locator('tr:nth-child(2) > td > p').first().click();
        await page.locator('td').nth(3).fill('cell1.5');

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).toContainText('cell1');
        await expect(page.locator('td').nth(3)).toContainText('cell1.5');
        await expect(page.locator('td').nth(6)).toContainText('cell2');
    });

    consoleTest('Create and add a row below', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(3).fill('cell2');
        await page.getByText('cell1').click();
        await page.locator('div').filter({ hasText: /^cell1cell2\+$/ }).getByRole('button').first().click();
        await page.getByRole('button', { name: 'Insert Below' }).click();
        await page.locator('tr:nth-child(2) > td > p').first().click();
        await page.locator('td').nth(3).fill('cell1.5');

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).toContainText('cell1');
        await expect(page.locator('td').nth(3)).toContainText('cell1.5');
        await expect(page.locator('td').nth(6)).toContainText('cell2');
    });

    consoleTest('Create and delete local row', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(3).fill('cell2');
        await page.getByText('cell1').click();
        await page.locator('div').filter({ hasText: /^cell1cell2\+$/ }).getByRole('button').first().click();
        await page.getByRole('button', { name: 'Insert Below' }).click();
        await page.locator('tr:nth-child(2) > td > p').first().click();
        await page.locator('td').nth(3).fill('cell1.5');
        await page.getByText('cell1.5', { exact: true }).click();
        await page.locator('div').filter({ hasText: /^cell1cell1\.5cell2\+$/ }).getByRole('button').first().click();
        await page.getByRole('button', { name: 'Delete row' }).click();

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).toContainText('cell1');
        await expect(page.locator('td').nth(3)).not.toContainText('cell1.5');
        await expect(page.locator('td').nth(3)).toContainText('cell2');
    });

    consoleTest('Create and delete local column', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(1).fill('cell2');
        await page.getByText('cell1').click();
        await page.locator('.toggle-table-menu-button').first().click();
        await page.getByRole('button', { name: 'Insert After' }).click();
        await page.getByRole('row', { name: 'cell1 cell2' }).getByRole('paragraph').nth(1).click();
        await page.locator('td').nth(1).fill('cell1.5');
        await page.getByRole('cell', { name: 'cell1.5' }).click();
        await page.locator('.toggle-table-menu-button').first().click();
        await page.getByRole('button', { name: 'Delete column' }).click();

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).toContainText('cell1');
        await expect(page.locator('td').nth(1)).not.toContainText('cell1.5');
        await expect(page.locator('td').nth(1)).toContainText('cell2');
    });

    consoleTest('Create and clear row', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(1).fill('cell2');
        await page.locator('td').nth(2).fill('cell3');
        await page.getByText('cell1').click();
        await page.locator('div').filter({ hasText: /^cell1cell2cell3\+$/ }).getByRole('button').first().click();
        await page.getByRole('button', { name: 'Clear content' }).click();

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).not.toContainText('cell1');
        await expect(page.locator('td').nth(1)).not.toContainText('cell2');
        await expect(page.locator('td').nth(2)).not.toContainText('cell3');
    });

    consoleTest('Create and clear column', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(3).fill('cell2');
        await page.locator('td').nth(6).fill('cell3');
        await page.getByText('cell1').click();
        await page.locator('.toggle-table-menu-button').first().click();
        await page.getByRole('button', { name: 'Clear content' }).click();

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).not.toContainText('cell1');
        await expect(page.locator('td').nth(3)).not.toContainText('cell2');
        await expect(page.locator('td').nth(6)).not.toContainText('cell3');
    });

    consoleTest('Create and merge cells', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(1).fill('cell2');
        await page.locator('td').nth(2).fill('cell3');
        await page.getByRole('cell', { name: 'cell1' }).click();
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowRight');
        await page.locator('.ProseMirror').press('ArrowRight');
        await page.keyboard.up('Shift');
        await page.locator('.merge-cell-button').first().click();

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).not.toContainText('cell1\ncell2\ncell3');
        await expect(page.locator('td').nth(1)).not.toContainText('cell2');
        await expect(page.locator('td').nth(3)).not.toContainText('cell2');
        await expect(page.locator('td').nth(6)).not.toContainText('cell3');
    });

    consoleTest('Create and merge and split cells', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').fill('/');
        await page.locator('div').filter({ hasText: /^TableAdd a table$/ }).first().click();
        await page.locator('p').first().click();
        await page.locator('p').first().fill('cell1');
        await page.locator('td').nth(1).fill('cell2');
        await page.locator('td').nth(2).fill('cell3');
        await page.getByRole('cell', { name: 'cell1' }).click();
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowRight');
        await page.locator('.ProseMirror').press('ArrowRight');
        await page.keyboard.up('Shift');
        await page.locator('.merge-cell-button').first().click();
        await page.getByText('cell3', { exact: true }).click();
        await page.locator('button:nth-child(2)').first().click();

        await expect(page.locator('table').first()).toBeVisible();
        await expect(page.locator('td').first()).not.toContainText('cell1\ncell2\ncell3');
        await expect(page.locator('td').nth(1)).toContainText('');
        await expect(page.locator('td').nth(2)).toContainText('');
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

test.describe('SEO', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('0% SEO', async ({testingApi, console, page}) => {
        await page.getByRole('button', { name: 'SEO 0%' }).click();
        await expect(page.getByText('0%').first()).toBeVisible();
    });

    consoleTest('20% adding primary keyword', async ({testingApi, console, page}) => {
        await page.getByRole('button', { name: 'SEO 0%' }).click();
        await page.locator('div').filter({ hasText: /^Primary Keyword\+ Add$/ }).getByRole('button', { name: '+ Add' }).click();
        await page.getByTestId('post-seo').getByRole('textbox').fill('post');
        await page.getByTestId('post-seo').locator('a').first().click();
        await page.getByRole('paragraph').click();
        await page.locator('.ProseMirror').fill('How to test a post');

        await expect(page.getByText('20%').first()).toBeVisible();
    });

    consoleTest('48% adding keyword in post data and heading', async ({testingApi, console, page}) => {
        await page.getByRole('button', { name: 'SEO 0%' }).click();
        await page.locator('div').filter({ hasText: /^Primary Keyword\+ Add$/ }).getByRole('button', { name: '+ Add' }).click();
        await page.getByTestId('post-seo').getByRole('textbox').fill('post');
        await page.getByTestId('post-seo').locator('a').first().click();
        await page.getByRole('paragraph').click();
        await page.locator('.ProseMirror').fill('How to test a post');
        await page.getByRole('button', { name: 'Settings' }).click();
        await page.getByTestId('slug-input').click();
        await page.getByTestId('slug-input').fill('post');
        await page.getByTestId('description-input').click();
        await page.getByTestId('description-input').fill('post');
        await page.getByTestId('posts').getByText('How to test a post').click();
        await page.locator('div').filter({ hasText: /^How to test a post$/ }).press('Enter');
        await page.keyboard.press('/');
        await page.locator('div').filter({ hasText: /^Heading - LargeTo divide main sections of the post$/ }).first().click();
        await page.keyboard.press('p');
        await page.keyboard.press('o');
        await page.keyboard.press('s');
        await page.keyboard.press('t');

        await expect(page.getByText('48%').first()).toBeVisible();
    });

    consoleTest('46% long post content length', async ({testingApi, console, page}) => {
        await page.getByRole('button', { name: 'SEO 0%' }).click();
        await page.locator('div').filter({ hasText: /^Primary Keyword\+ Add$/ }).getByRole('button', { name: '+ Add' }).click();
        await page.getByTestId('post-seo').getByRole('textbox').fill('post');
        await page.getByTestId('post-seo').locator('a').first().click();
        await page.getByRole('paragraph').click();
        await page.locator('.ProseMirror').fill('How to test a post');
        await page.getByRole('button', { name: 'Settings' }).click();
        await page.getByTestId('slug-input').click();
        await page.getByTestId('slug-input').fill('post');
        await page.getByTestId('description-input').click();
        await page.getByTestId('description-input').fill('post');
        await page.getByText('How to test a post').fill('How to test a post\n\npost\nh2#\n\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce facilisis, arcu quis ultricies gravida, erat purus scelerisque nunc, nec pellentesque justo elit eu dui. Vestibulum vehicula odio eu semper. Nulla facilisi. Sed varius justo ut nisl euismod, eu rhoncus dui malesuada. Aliquam erat volutpat. Nunc sollicitudin lacus ut tellus efficitur, at venenatis libero semper. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum quis fermentum massa, vel sollicitudin purus. Sed vel ipsum vel justo tincidunt consectetur eu ac felis. Nullam eu sagittis velit. Curabitur vitae ligula id nunc cursus rhoncus. Vivamus nec odio a urna egestas bibendum. Nulla lacinia nulla ut nisi auctor, a eleifend metus bibendum. Integer eu nulla in sapien feugiat tincidunt.Donec auctor, dui vel iaculis bibendum, justo tortor interdum justo, in varius libero libero sit amet urna. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Integer laoreet, enim in egestas luctus, erat eros ullamcorper risus, ac tempor nunc tortor in odio. Sed vel libero ac nunc laoreet tincidunt. In ultricies velit at odio posuere, eu convallis elit tincidunt. Fusce vestibulum, elit sit amet feugiat fringilla, urna justo facilisis metus, a elementum sapien ex nec lectus. Vivamus ultricies semper enim id tincidunt. Vivamus egestas vehicula augue, eu condimentum enim vulputate eu. Proin non nisl sit amet dui viverra efficitur ac sit amet odio. Nulla vehicula eleifend mi, eu consequat metus vehicula a. Nullam a odio vel metus lacinia tristique. Sed mattis quam quis ex dignissim, nec pharetra orci lacinia. Fusce eu hendrerit orci, non consequat orci.Phasellus bibendum, quam eu dignissim vulputate, metus urna interdum nunc, non semper quam lorem eu lectus. Curabitur euismod magna in quam efficitur, vel vestibulum dui sagittis. Nunc nec ex nec purus bibendum cursus vel nec ligula. Nulla facilisi. Vestibulum non lorem in metus aliquam gravida. Vestibulum bibendum pharetra ipsum, eget varius nulla dictum in. Fusce ut metus ac felis auctor dapibus. Sed bibendum ex sit amet quam blandit, at tincidunt nulla gravida. Sed venenatis turpis quis velit auctor, sit amet euismod ante vulputate. Fusce convallis arcu ut lacinia scelerisque. Aenean ullamcorper ante in dictum efficitur. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Sed rhoncus nec urna nec pharetra. Nulla facilisi. Curabitur vel purus in libero vulputate eleifend. Nullam et tincidunt ipsum. Suspendisse semper orci vel justo scelerisque, nec dictum augue vulputate.Proin ullamcorper, ipsum in dictum venenatis, velit ante malesuada quam, quis venenatis lectus lorem ut ipsum. Integer sed arcu non tortor fermentum cursus. Integer elementum, odio quis efficitur fermentum, eros nisi dignissim leo, a vestibulum metus est id est. Suspendisse potenti. Sed laoreet bibendum dui id scelerisque. Vestibulum tincidunt ante eu turpis feugiat, in euismod odio vehicula. Sed semper scelerisque libero, a tempus libero venenatis at. Sed nec euismod erat. Sed nec bibendum tortor, in commodo nunc. Suspendisse eget justo ut odio dapibus suscipit. Sed tristique eleifend nulla, eget sollicitudin nisi finibus a. Nulla facilisi. Curabitur scelerisque, lectus eget vestibulum consectetur, nulla dolor rhoncus ante, vel finibus libero velit sit amet tellus. Vivamus eu tellus sit amet ante hendrerit tincidunt vel eget metus. In nec ipsum in arcu euismod venenatis. Sed non neque id sapien mattis fringilla eget eu ex.');

        await expect(page.getByText('46%').first()).toBeVisible();
    });
});

test.describe('Links', () => {

    consoleTest.beforeEach(async ({testingApi, console, page}) => {
        await testingApi.factory.testPost();
        await console.visitAndNav('posts');
        await page.getByRole('link', { name: 'Test Post' }).click();
        await page.locator('.ProseMirror').fill(''); 
    });

    consoleTest('Add a link', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('https://www.google.fr/');
        await page.keyboard.press('Enter');

        await page.getByRole('button', { name: 'Links' }).click();
        await expect(page.getByText('Links (1)')).toBeVisible();
        await expect(page.getByText('https://www.google.fr/external')).toBeVisible();
    });

    consoleTest('Refresh a link', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('https://www.google.fr/');
        await page.keyboard.press('Enter');

        await page.getByRole('button', { name: 'Links' }).click();
        await page.locator('span').filter({ hasText: 'Recheck' }).getByRole('button').click();
        await expect(page.getByText('Links (1)')).toBeVisible();
        await expect(page.getByText('https://www.google.fr/external')).toBeVisible();
    });

    consoleTest('Delete a link', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('https://www.google.fr/');
        await page.keyboard.press('Enter');

        await page.getByRole('button', { name: 'Links' }).click();
        await page.locator('span').filter({ hasText: 'Edit in Editor' }).getByRole('button').click();
        await page.keyboard.press('Backspace');
        await expect(page.getByText('Links (1)')).not.toBeVisible();
        await expect(page.getByText('https://www.google.fr/external')).not.toBeVisible();
    });

    consoleTest('Ignrore a link', async ({testingApi, console, page}) => {
        await page.locator('.ProseMirror').click();
        await page.locator('.ProseMirror').fill('Over');
        await page.keyboard.down('Shift');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.ProseMirror').press('ArrowLeft');
        await page.locator('.pm-tooltip > span:nth-child(1)').first().click();
        await page.getByPlaceholder('Enter a link...').fill('https://www.google.fr/');
        await page.keyboard.press('Enter');

        await page.getByRole('button', { name: 'Links' }).click();
        await page.locator('span').filter({ hasText: 'Ignore this link' }).getByRole('button').click();
    });
});

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