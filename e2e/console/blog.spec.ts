import { expect, test } from "@playwright/test";
import consoleTest from "./consoleTest";

test.describe(() => {

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

})
