import { chromium, expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";


consoleTest('creates a blog', async ({ console, page }) => {

    await console.visit();
    await expect(page).toHaveTitle('Console - Hyvor Blogs');
    await expect(page.getByText('Start a new blog')).toBeVisible();

    await page.getByLabel('Blog Name').fill('My First Blog');
    await expect(page.getByLabel('Subdomain')).toHaveValue('my-first-blog');

    await page.getByText('Create Blog').click();
    await expect(page.getByText('My First Blog').nth(0)).toBeVisible();

});

consoleTest('fails when a subdomain is taken', async ({ console, page, testingApi }) => {

    const {blog} = await testingApi.factory.blogFull();

    await console.visit();
    await page.getByText('Create New Blog').click();

    await page.getByLabel('Blog Name').fill('My First Blog');
    await page.getByLabel('Subdomain').fill(blog.subdomain);

    await expect(page.getByText('Subdomain already taken')).toBeVisible();

    await page.getByText('Create Blog').click();
    await expect(page.getByText('Try Again')).toBeVisible();

    await page.getByLabel('Subdomain').fill(blog.subdomain + "-2");
    await page.getByText('Try Again').click();
    await expect(page.getByText('My First Blog').nth(0)).toBeVisible();

});

consoleTest('Blog switching', async ({ console, page, testingApi }) => {
    /* Create the first blog */
    await console.visit();
    await expect(page).toHaveTitle('Console - Hyvor Blogs');
    await expect(page.getByText('Start a new blog')).toBeVisible();

    await page.getByLabel('Blog Name').fill('My First Blog');
    await expect(page.getByLabel('Subdomain')).toHaveValue('my-first-blog');

    await page.getByText('Create Blog').click();
    await expect(page.getByText('My First Blog').nth(0)).toBeVisible({timeout: 30000});

    /* Create the second blog */
    await page.locator('div').filter({ hasText: /^My First Blog$/ }).first().click();
    await page.getByRole('button', { name: 'Create a blog' }).click();

    await page.getByLabel('Blog Name').fill('My Second Blog');
    await expect(page.getByLabel('Subdomain')).toHaveValue('my-second-blog');
    await page.getByText('Create Blog').click();
    await expect(page.getByText('My Second Blog').nth(0)).toBeVisible({timeout: 30000});

    /* Switch to the first blog */
    await page.locator('div').filter({ hasText: /^My Second Blog$/ }).first().click();
    await page.locator('.blog').first().click();
    await expect(page.getByText('My First Blog').nth(0)).toBeVisible();
    
});

consoleTest('Blog Drag and dropping', async ({ console, page, testingApi }) => {
    /* Create the first blog */
    await console.visit();
    await expect(page).toHaveTitle('Console - Hyvor Blogs');
    await expect(page.getByText('Start a new blog')).toBeVisible();

    await page.getByLabel('Blog Name').fill('My First Blog');
    await expect(page.getByLabel('Subdomain')).toHaveValue('my-first-blog');

    await page.getByText('Create Blog').click();
    await expect(page.getByText('My First Blog').nth(0)).toBeVisible({timeout: 30000});

    /* Create the second blog */
    await page.locator('div').filter({ hasText: /^My First Blog$/ }).first().click();
    await page.getByRole('button', { name: 'Create a blog' }).click();

    await page.getByLabel('Blog Name').fill('My Second Blog');
    await expect(page.getByLabel('Subdomain')).toHaveValue('my-second-blog');
    await page.getByText('Create Blog').click();
    await expect(page.getByText('My Second Blog').nth(0)).toBeVisible({timeout: 30000});

        /* Drag blog test */
    await page.locator('div').filter({ hasText: /^My Second Blog$/ }).first().click();
    const sourceElement = await page.locator('.blog').first();
    const targetElement = await page.locator('.blog-list > div > div:nth-child(2)');


    if (sourceElement && targetElement) {
        const srcBound = await sourceElement.boundingBox();

        const targetBound = await targetElement.boundingBox();
        if (srcBound && targetBound) {
            await page.waitForTimeout(1000); 
            await sourceElement.dragTo(sourceElement, {
                force: true,
                sourcePosition: {
                    x: srcBound.width / 2,
                    y: 0
                },
                targetPosition: {
                    x: 0,
                    y: 50,
                },
                timeout: 30000,
                trial: true
            })
        }
        else {
            throw new Error('Source or target element is not visible');
        }
    }
    else {
        throw new Error('Source or target element is not visible');
    }
});


consoleTest('redirects to console home when subdomain not found', async ({ console, page, testingApi }) => {

    const {blog} = await testingApi.factory.blogFull();

    await console.visit('/invalid-subdomain');

    await expect(page.url()).toContain('/console');
    await expect(page.url()).not.toContain('invalid-subdomain');

});