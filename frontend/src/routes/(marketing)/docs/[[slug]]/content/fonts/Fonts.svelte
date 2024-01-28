<script>
	import { Callout, CodeBlock, Tag } from "@hyvor/design/components";
	import { DocsImage } from "@hyvor/design/marketing";


    import fontsSelectImg from './fonts-select.png';
    import fontsConfig from './fonts-config.png';
    import fontsEmbedCss from './fonts-embed-css.png';
    import fontsUse from './fonts-use.png';
</script>
<h1>Fonts</h1>

<p>
    All themes in Hyvor Blogs comes with a default font. You can easily change it to any font you want. There are two ways to change the font.
</p>

<ul>
    <li>
        <a href="#google-fonts">Google Fonts</a>
    </li>
    <li>
        <a href="#custom-fonts">Custom Fonts</a>
    </li>
</ul>

<h2 id="google-fonts">
    1. Local Google Fonts
</h2>

<p>
    <a href="https://fonts.google.com/" target="_blank" rel="nofollow">Google Fonts</a> is the most popular service for web fonts. <strong>However</strong>, it has known privacy and compliancy issues with GDPR and CCPA. Therefore, we proxy Google Fonts through our servers to provide you with better privacy, compliancy, and performance. All scripts or stylesheets will be served directly from your blog's domain.
</p>

<ul>
    <li>
        <code>{`https://[your-domain]/fonts/css/{family}`}</code> - CSS Proxy
    </li>
    <li>
        <code>{`https://[your-domain]/fonts/file/{file_name}`}</code> - Font File Proxy
    </li>
</ul>

<p>
    Note: Due to some limitations in Google TOS, under the hood, we proxy <a href="https://fonts.bunny.net/" target="_blank" rel="nofollow">Bunny Fonts</a>, which is a privacy-focused alternative to Google Fonts. Both services provide the same fonts with the same API. Variable fonts are not supported.
</p>

<h3 id="load-fonts">Step 1: Load Fonts</h3>

<p>
    First, head over to <a href="https://fonts.bunny.net/" rel="nofollow" target="_blank">Bunny Fonts</a> (or Google Fonts) and select the fonts and variants you like. You will see a CSS code to load the fonts as below.
</p>

<CodeBlock code="@import url(https://fonts.bunny.net/css?family=mulish:400,700);" language="css" />

<p>
    From this, copy only the family part. In this case, it is <code>mulish:400,700</code>.
</p>

<DocsImage src={fontsSelectImg} alt="Select font on bunny fonts" />


<p>
    Then, paste it in <strong>Theme → config.yaml → THEME_FONTS</strong> in the Hyvor Blogs Console.
</p>


<DocsImage src={fontsConfig} alt="Config fonts" />

<p>
    When you add this to the config.yaml, the fonts will be loaded in the <code>{`<head>`}</code> tag of your blog automatically. Then, you can use that font in your blog.
</p>


<Callout type="info">
    Note: If you do not see the Theme Fonts option in the UI, switch to YAML mode (top right corner) and add <code>THEME_FONTS</code> option after <code>THEME_VERSION</code>.

    <CodeBlock code={`
        THEME_NAME: hello
        THEME_VERSION: 1.0.0
        THEME_FONTS: "mulish:400,700"
    `} language="yaml" />
</Callout>

<h3 id="use-fonts">Step 2: Use Fonts</h3>

<p>
    All official themes support font customization via <code>config.yaml</code>. Some themes have multiple font options for different parts of the blog (ex: text vs headings).
</p>


<p>
    Copy the font family value from the <strong>Embed CSS</strong> section of Bunny Fonts.
</p>

<DocsImage src={fontsEmbedCss} alt="Embed CSS" width={300} />

<p>
    Paste it in the relevant option in <code>config.yaml</code> in your theme.
</p>

<DocsImage src={fontsUse} alt="Use fonts" />

<p>
    If your theme does not support font customization, you can use CSS to change the font as explained in the next section.
</p>

<h2 id="custom-fonts">
    2. Custom Fonts
</h2>

<p>
    If you need to add a custom font not available in Google Fonts, follow these steps:
</p>

<ul>
    <li>
        Upload the font files to <strong>Theme → assets</strong>
    </li>
    <li>
        Add custom CSS to a SCSS file in <strong>Theme → assets</strong>
    </li>
</ul>

<p>
    Your custom CSS should look like this:
</p>

<CodeBlock code={`
    @font-face {
        font-family: 'My Font';
        src: url('/assets/my-font.woff2') format('woff2');
        font-weight: normal;
        font-style: normal;
    }
    body {
        font-family: 'My Font', sans-serif;
    }
`} language="css" />