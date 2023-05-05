import { expect, test } from "@playwright/test";

const CONFIG_YAML = `
THEME_NAME: hello

colors:
    accent: "#000"

fonts:
    family: "Mulish"
    size: 16

settings:
    loop: true

image: photo_only
`

const CONFIG_DEF_YAML = `
colors:
    $name: Colors
    accent:
        $name: Accent Color
        $description: Base color for the blog
        $type: color
    
fonts:
    $name: Fonts
    family:
        $name: Font Family
        $description: Font family for the blog
        $maxlength: 10
        $minlength: 1
    size:
        $name: Font Size
        $description: Font size for the blog
        $type: number
        $max: 100
        $min: 10

settings:
    $name: Settings
    loop:
        $name: Loop
        $description: Show posts loop
        $type: checkbox

image:
    $name: Image
    $description: Image in the post
    $type: radio
    $options:
        photo_only: Photo Only
        photo_with_caption: Photo with Caption
        photo_with_text: Photo with Text
`


test('config def', async ({page}) => {

    await page.goto('/config');

    await page.getByTestId('config-editor').click();
    await page.locator('*:focus').fill(CONFIG_YAML);

    await page.getByTestId('config-def-editor').click();
    await page.locator('*:focus').fill(CONFIG_DEF_YAML);

    // $type = color

    const colors = await page.getByTestId('config-colors');
    await expect(colors.getByText('Colors')).toBeVisible();

    const accentColor = await page.getByTestId('config-colors.accent');
    await expect(accentColor.getByText('Accent Color')).toBeVisible();
    await expect(accentColor.getByText('Base color for the blog')).toBeVisible();

    const colorPicker = await accentColor.getByTestId('color-picker-preview');
    await colorPicker.click();

    const input = await accentColor.locator('input[value="000000"]').first();
    await input.fill('ff0000');
    await page.mouse.click(0, 0);


    // $type = text

    const fontFamilyInput = await page.getByTestId('config-fonts.family').locator('input');
    await fontFamilyInput.fill('Arial Arial');
    await expect(fontFamilyInput).toHaveValue('Arial Aria'); // max length
    await expect(fontFamilyInput).toHaveAttribute('minlength', "1");

    // $type = number

    const fontSizeInput = await page.getByTestId('config-fonts.size').locator('input');
    await fontSizeInput.fill('18');
    await expect(fontSizeInput).toHaveAttribute('min', "10");
    await expect(fontSizeInput).toHaveAttribute('max', "100");

    // $type = checkbox

    const loopCheckbox = await page.getByTestId('config-settings.loop').locator('input');
    await expect(loopCheckbox).toBeChecked();
    await page.getByTestId('config-settings.loop').getByTestId('switch').click();
    await expect(loopCheckbox).not.toBeChecked();

    // $type = radio

    const imageRadioInputs = await page.getByTestId('config-image').locator('input');

    await expect(imageRadioInputs.nth(0)).toBeChecked();
    await expect(imageRadioInputs.nth(1)).not.toBeChecked();
    await expect(imageRadioInputs.nth(2)).not.toBeChecked();

    const imageRadioWraps = await page.getByTestId('config-image').getByTestId('radio');

    await imageRadioWraps.nth(1).click();
    await expect(imageRadioInputs.nth(0)).not.toBeChecked();
    await expect(imageRadioInputs.nth(1)).toBeChecked();


});