# Fonts

There are two ways to add fonts to your blog:

* [Add Google Fonts (Proxied)](#google-fonts)
* [Add Custom Fonts](#custom-fonts)

## Add Google Fonts (Proxied) {#google-fonts}

[Google Fonts](https://fonts.google.com/) is the most popular service for web fonts. However, it has known privacy issues and is not compliant with privacy laws like GDPR. Therefore, we proxy Google Fonts to provide you with a better privacy and speed. All scripts or stylesheets will be served them directly from your blog's domain.

* `https://[your-domain]/fonts/css/{family}` - CSS Proxy
* `https://[your-domain]/fonts/file/{file_name}` - Font file proxy

Due to some limitations in Google TOS, under the hood, we proxy [Bunny Fonts](https://fonts.bunny.net), which is a privacy-focused alternative to Google Fonts. Both services provide the same fonts with the same API. Variable fonts are not supported.

### How to load fonts in your blog

#### 1. Load the Fonts

First, head over to [Bunny Fonts](https://fonts.bunny.net/) (or Google Fonts) and select the fonts and variants you need. You will see a CSS code to load the fonts as below.

```css
@import url(https://fonts.bunny.net/css?family=mulish:400,700);
```

From this, copy only the family part. In this case, it is `mulish:400,700`.

![Use Fonts in config.yaml](/img/docs/fonts-select.png)

Then, paste it in **Console &rarr; Theme &rarr; config.yaml &rarr; THEME_FONTS**.

![Use Fonts in config.yaml](/img/docs/fonts-config.png)

When you add this to the config.yaml, the fonts will be loaded in the `<head>` tag of your blog automatically. Then, you can use that font in your blog.

> Note: If you do not see the Theme Fonts option in the UI, switch to YAML mode (top right corner) and add `THEME_FONTS` option after `THEME_VERSION`.
>
> ```yaml
> THEME_NAME: hello
> THEME_VERSION: 1.0.0
> THEME_FONTS: "mulish:400,700"
> ```

#### 2. Use the fonts

This steps depends on the theme you use. All official themes support font customization via `config.yaml`. Some themes have multiple font options for different parts of the blog (ex: text vs headings). 

Copy the font family value from the **Embed CSS** section of Bunny Fonts.


<p>
    <img src="/img/docs/fonts-embed-css.png" alt="Embed CSS in Bunny Fonts" width="300px">
</p>

Paste it in the relevant option in `config.yaml` in your theme.

![Use Fonts in config.yaml](/img/docs/fonts-use.png)


> If your theme does not support font customization, you can use CSS to change the font.


## Add Custom Fonts {#custom-fonts}

If you need to add a custom font not available in Google Fonts, follow these steps:

* Upload the font files to **Console &rarr; Theme &rarr; assets**
* Add custom CSS to a SCSS file in **Console &rarr; Theme &rarr; styles**

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