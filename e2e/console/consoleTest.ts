import { TestingApi } from './../baseTest';
import { Page } from "@playwright/test";
import baseTest from "../baseTest.ts";

export const consoleTest = baseTest.extend<{console: Console}>({
    console: async ({page, testingApi}, use) => {

        await testingApi.truncate();

        const console = new Console(page, testingApi);
        await use(console);

    }
});

type NavType = 'billing' | 'posts' | 'settings' | 'tools';

class Console {


    constructor(private page: Page, private testingApi: TestingApi) {}

    async visit(path : string = '') {
        await this.page.goto('/console' + path);
    }

    async visitAndNav(nav: NavType) {
        await this.visit();
        await this.nav(nav);
    }

    async nav(nav: NavType) {
        await this.page.getByTestId('main-nav-' + nav).click();
    }

    async visitNewPost(attrs = {}) {
        await this.testingApi.factory.testPost(attrs);
        await this.visitAndNav('posts');
        await this.page.getByRole('link', { name: 'Test Post' }).click();
    }
}