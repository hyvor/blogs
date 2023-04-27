import { Page } from "@playwright/test";
import baseTest from "../baseTest";

const consoleTest = baseTest.extend<{console: Console}>({
    console: async ({page, testingApi}, use) => {

        await testingApi.truncate();

        const console = new Console(page);
        await use(console);

    }
});

class Console {

    private page : Page;

    constructor(page) {
        this.page = page;
    }

    async visit() {
        await this.page.goto('/console');
    }

    async nav() {
        // todo
    }

}

export default consoleTest;