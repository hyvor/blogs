import { expect, test } from "@playwright/test";
import {consoleTest} from "./consoleTest.ts";


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
        await expect(page.getByRole('button', {name: "Upgrade"})).toHaveCount(6);

        await console.nav('posts');

        await expect(page.getByText('Your trial has ended')).toBeVisible();
        await page.getByText('Go to Billing').click();

        await expect(page.getByText('Trial Ended')).toBeVisible();

    });

    consoleTest('has subscription', async ({testingApi, console, page}) => {

        const {blog} = await testingApi.factory.blogFull();
        const subscription = await testingApi.factory.subscription({
            blog_id: blog.id,
            meta: '{"paddle_subscription_id": "123"}',
            plan: 'premium'
        });

        await console.visitAndNav('billing');

        const currentPlan = page.locator('.plan.current');
        await expect(currentPlan).toHaveCount(1);
        await expect(currentPlan.getByText('Premium')).toBeVisible();

        await currentPlan.getByText('Cancel').click();
        await expect(page.getByText('Are you sure you want to cancel the subscription?')).toBeVisible();

        await page.route('**/billing/paddle/subscription', route => route.fulfill());

        // cancel subscription to test
        const endsAt = (new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().split('T')[0]); // tomorrow
        await testingApi.query(`
            UPDATE subscriptions 
            SET status = 'deleted', ends_at = "${endsAt}"
            WHERE id = ${subscription.id}`
        );

        await page.getByRole('button', { name: "Cancel Subscription" }).click();

        await expect(page.getByText('Subscription Canceled')).toBeVisible();

    });

    consoleTest('upgraded manually', async ({testingApi, console, page}) => {

        // (no Paddle subscription ID)

        const {blog} = await testingApi.factory.blogFull({
            blogAttrs: {
                trial_ends_at: (new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString()) // yesterday
            }
        });
        await testingApi.factory.subscription({
            blog_id: blog.id,
        });

        await console.visitAndNav('billing');

        await expect(page.getByText('Manually Upgraded')).toHaveCount(2);
        await expect(page.getByText('Switch')).toHaveCount(0); // Plan switch buttons are hidden

        // cancels directly
        await page.getByText('Cancel', {exact: true}).click();
        await expect(page.getByText('Are you sure you want to cancel the subscription?')).toBeVisible();
        await page.getByRole('button', { name: "Cancel Subscription" }).click();

        await expect(page.getByText('Trial Ended')).toBeVisible();

    });

    consoleTest('canceled but not expired', async ({testingApi, console, page}) => {

        const {blog} = await testingApi.factory.blogFull();
        await testingApi.factory.subscription({
            blog_id: blog.id,
            status: 'deleted',
            ends_at: (new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString()), // tomorrow
            meta: '{"paddle_subscription_id": "123"}'
        });

        await console.visitAndNav('billing');

        await expect(page.getByText('Subscription Canceled')).toBeVisible();

        await page.getByText('Cancel', {exact: true}).click();
        await expect(page.getByText('Are you sure you want to force cancel the subscription now?')).toBeVisible();
        await page.getByRole('button', { name: "Cancel Subscription" }).click();

        await expect(page.getByText('Trial ends in')).toBeVisible();

    });

    consoleTest('past due', async ({testingApi, console, page}) => {

        const {blog} = await testingApi.factory.blogFull();
        await testingApi.factory.subscription({
            blog_id: blog.id,
            status: 'past_due',
            meta: '{"paddle_subscription_id": "123"}'
        });

        await console.visitAndNav('billing');

        await expect(page.getByTestId('nav-subscription-issue-icon')).toBeVisible();
        await expect(page.getByText('Payment Past Due')).toBeVisible();

    });

});


test.describe('usage', () => {

    

});