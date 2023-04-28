import { test } from "@playwright/test";

const CONFIG_YAML = `
THEME_NAME: hello
`


test('config def', async ({page}) => {

    await page.goto('/config');

    const configCodeWrap = await page.getByTestId('config-editor').nth(0);
    await configCodeWrap.click();
    await page.locator('*:focus').fill(CONFIG_YAML);


});