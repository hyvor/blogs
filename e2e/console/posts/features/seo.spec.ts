
import { expect, test } from "@playwright/test";
import {consoleTest} from "../../consoleTest.ts";


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