import { Page } from "@playwright/test";
import baseTest from "../baseTest";

const consoleTest = baseTest.extend<{console: Console}>({
    console: async ({page, testingApi}, use) => {

        await testingApi.truncate();

        const console = new Console(page);
        await use(console);

    }
});

type NavType = 'billing' | 'posts';

class Console {

    private page : Page;

    constructor(page) {
        this.page = page;
    }

    async visit() {
        await this.page.goto('/console');
    }

    async visitAndNav(nav: NavType) {
        await this.visit();
        await this.nav(nav);
    }

    async nav(nav: NavType) {
        await this.page.getByTestId('main-nav-' + nav).click();
    }

}

export default consoleTest;