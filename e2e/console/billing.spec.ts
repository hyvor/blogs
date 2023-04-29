import { expect, test } from "@playwright/test";
import consoleTest from "./consoleTest";


test.describe('plans', () => {

    consoleTest('in trial', async ({testingApi, console, page}) => {

        await testingApi.factory.blogFull();
        await console.visitAndNav('billing');

        await expect(page.getByText('Trial ends in 6 days')).toBeVisible();
        await expect(page.getByTestId('subscription-issue-icon')).toHaveCount(0);

    });

    consoleTest('trial ended', async ({testingApi, console, page}) => {

        await testingApi.factory.blogFull({
            blogAttrs: {
                // yesterday
                trial_ends_at: (new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString())
            }
        });
        await console.visitAndNav('billing');

        await expect(page.getByText('Trial Ended')).toBeVisible();
        await expect(page.getByText('Your trial has ended. Upgrade now to continue using your blog.')).toBeVisible();

        // subscription issue icon in the navigation
        await expect(page.getByTestId('nav-subscription-issue-icon')).toBeVisible();

        await console.nav('posts');

        await expect(page.getByText('Your trial has ended')).toBeVisible();
        await page.getByText('Go to Billing').click();

        await expect(page.getByText('Trial Ended')).toBeVisible();

    });

});