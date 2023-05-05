import { APIRequestContext, Request, test } from "@playwright/test";
import playwrightConfig from "../playwright.config.ts";

const baseTest = test.extend<{testingApi: TestingApi}>({
    testingApi: async ({request}, use) => {

        const testingApi = new TestingApi(request);
        await use(testingApi);

    }
});

class TestingApi {

    factory: Factory;

    constructor(private request: APIRequestContext) {
        this.factory = new Factory(this);
    }

    async call(endpoint: string, data?: any) {
        const response = await this.request.post(playwrightConfig.use?.baseURL + '/_testing/' + endpoint, {data});

        if (response.status() !== 200) 
            throw new Error('Testing API call failed: ' + response.status() + ' ' + response.statusText());

        const json = await response.json();
        return json;
    }

    async truncate() {
        await this.call('truncate');
    }

    async callFactory(model: string, attrs: Record<string, any> = {}) {
        return await this.call('factory', {
            model,
            attrs
        });
    }

    async query(q: string) {
        return await this.call('query', {
            query: q
        });
    }

}

class Factory {

    constructor(private testingApi: TestingApi) {}

    // blog + variant + user + language + routes
    async blogFull({
        blogAttrs = {},
    } = {}) {
        const blog = await this.blog(blogAttrs);
        const user = await this.user({
            blog_id: blog.id,
            hyvor_user_id: 1,
            role: 'owner',
            status: 'active'
        });

        const language = await this.language({
            blog_id: blog.id,
            is_primary: true,
        });


        const variant = await this.blogVariant({
            blog_id: blog.id,
            language_id: language.id,
        });

        return {
            blog,
            user,
            language,
            variant
        }
    }

    async blog(attrs = {}) {
        return await this.testingApi.callFactory('Blog', {hyvor_user_id: 1, ...attrs});
    }

    async blogVariant(attrs = {}) {
        return await this.testingApi.callFactory('BlogVariant', attrs);
    }

    async language(attrs = {}) {
        return await this.testingApi.callFactory('Language', attrs);
    }

    async user(attrs = {}) {
        return await this.testingApi.callFactory('User', attrs);
    }


    async subscription(attrs = {}) {
        return await this.testingApi.callFactory('Subscription', attrs);
    }
    
}

export default baseTest;