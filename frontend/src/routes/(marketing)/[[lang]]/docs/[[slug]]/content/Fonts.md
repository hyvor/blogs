<script>
	import { Callout, CodeBlock } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Fonts

All themes in Hyvor Blogs comes with a default font. You can easily change it to any font you want. There are two ways to change the font.

- [Google Fonts](#google-fonts)
- [Custom Fonts](#custom-fonts)

<h2 id="google-fonts">1. Local Google Fonts</h2>

<a href="https://fonts.google.com/" target="_blank" rel="nofollow">Google Fonts</a> is the most popular service for web fonts. **However**, it has known privacy and compliancy issues with GDPR and CCPA. Therefore, we proxy Google Fonts through our servers to provide you with better privacy, compliancy, and performance. All scripts or stylesheets will be served directly from your blog's domain.

- `https://[your-domain]/fonts/css/{family}` - CSS Proxy
- `https://[your-domain]/fonts/file/{file_name}` - Font File Proxy

Note: Due to some limitations in Google TOS, under the hood, we proxy <a href="https://fonts.bunny.net/" target="_blank" rel="nofollow">Bunny Fonts</a>, which is a privacy-focused alternative to Google Fonts. Both services provide the same fonts with the same API. Variable fonts are not supported.

<h3 id="load-fonts">Step 1: Load Fonts</h3>

First, head over to <a href="https://fonts.bunny.net/" rel="nofollow" target="_blank">Bunny Fonts</a> (or Google Fonts) and select the fonts and variants you like. You will see a CSS code to load the fonts as below.

```css
@import url(https://fonts.bunny.net/css?family=mulish:400,700);
```

From this, copy only the family part. In this case, it is `mulish:400,700`.

<DocsImage src="/images/docs/fonts/fonts-select.png" alt="Select font on bunny fonts" />

Then, paste it in **Theme → config.yaml → THEME_FONTS** in the Hyvor Blogs Console.

<DocsImage src="/images/docs/fonts/fonts-config.png" alt="Config fonts" />

When you add this to the config.yaml, the fonts will be loaded in the `<head>` tag of your blog automatically. Then, you can use that font in your blog.

<Callout type="info">
	Note: If you do not see the Theme Fonts option in the UI, switch to YAML mode (top right corner)
	and add <code>THEME_FONTS</code> option after <code>THEME_VERSION</code>.

	<CodeBlock
		code={`
        THEME_NAME: hello
        THEME_VERSION: 1.0.0
        THEME_FONTS: "mulish:400,700"
    `}
		language="yaml"
	/>
</Callout>

<h3 id="use-fonts">Step 2: Use Fonts</h3>

All official themes support font customization via `config.yaml`. Some themes have multiple font options for different parts of the blog (ex: text vs headings).

Copy the font family value from the **Embed CSS** section of Bunny Fonts.

<DocsImage src="/images/docs/fonts/fonts-embed-css.png" alt="Embed CSS" width={300} />

Paste it in the relevant option in `config.yaml` in your theme.

<DocsImage src="/images/docs/fonts/fonts-use.png" alt="Use fonts" />

If your theme does not support font customization, you can use CSS to change the font as explained in the next section.

<h2 id="custom-fonts">2. Custom Fonts</h2>

If you need to add a custom font not available in Google Fonts, follow these steps:

- Upload the font files to **Theme → assets**
- Add custom CSS to a SCSS file in **Theme → assets**

Your custom CSS should look like this:

```css
@font-face {
    font-family: 'My Font';
    src: url('/assets/my-font.woff2') format('woff2');
    font-weight: normal;
    font-style: normal;
}
body {
    font-family: 'My Font', sans-serif;
}
```
