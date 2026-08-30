<script lang="ts">
	import { Callout } from '@hyvor/design/components';
</script>

# Styling Themes

This folder contains SCSS files. `index.scss is required.`

While developing and working with other blogging platforms/CMSs, we understood that customizing a theme becomes really hard when the theme developer puts all CSS in a single file. Therefore, we decided that we want to support "chunk-css" files to make it easy to edit for the blogger. And, we use <a href="https://sass-lang.com/" rel="nofollow">SCSS</a> instead of CSS to make the theme developer's life easier. All CSS is valid SCSS. So, if you haven't use SCSS earlier, just use CSS. SCSS just have some cool features like nesting rules.

Back to “chunk-css”. let’s say you make a partial file for the blog header (`templates/_header.twig`). Then create an SCSS file to hold its CSS (`header.scss`). This pattern makes understanding and editing easier for the blogger. Finally, import all chunk files to `index.scss` using `@import` statements.

```css
@import 'css-variables.scss';
@import 'header.scss';
@import 'body.scss';
```

On our side, we process `index.scss` file and generate a `styles.css`, which will be accessible via <ocde>/styles.css</ocde>. **That is the only CSS file of the whole blog!**

<Callout type="info">
	<p>
		We strongly encourage you to write CSS from scratch without using any libraries like Bootstrap.
		A blog theme is very simple and it is totally possible to manage everything on your own without
		depending on third-party libraries. If you really want to use a library, add it to assets
		instead of styles.
	</p>
</Callout>

<h2 id="fonts">Fonts</h2>

The easiest way to load fonts is by adding `THEME_FONTS` to the [config](/docs/themes-config) file.

```yaml
THEME_FONTS: "mulish:400"
```

Then, you can use the font in your SCSS files. See our [fonts](/docs/fonts) page for a in-depth guide.

<h2 id="advanced-nodes">Advanced Nodes</h2>

[Writing](/docs/writing) page describes all supported nodes. We try to use the most basic HTML elements to represent each node. However, there are some advanced components that require some attention when writing styles.

<h3 id="image">Image</h3>

```html
<figure>
    <img src="https://exmaple.com/image.png" />
    <figcaption>Here goes the caption</figcaption>
</figure>
```

Note that figcaption can be empty. So, check if margins look good when figcaption is not there.

<h3 id="embed-rich">Embed</h3>

```html
<figure>
    <div class="rich-embed">
        {# embed HTML code goes here... #}
    </div>
    <figcaption>Here goes the caption</figcaption>
</figure>
```

<h3 id="embed-link">Link Bookmark</h3>

```html
<figure>
    <a class="rich-link">
        <div class="rich-link-details">
            <div class="rich-link-title">{{ data.title }}</div>
            <div class="rich-link-description">{{ data.description }}</div>
            <div class="rich-link-domain">{{ data.domain }}</div>
        </div>
        <div class="rich-link-thumbnail">
            <img src="{{ data.thumbnail }}" />
        </div>
    </a>
    <figcaption>{{ data.caption }}</figcaption>
</figure>
```

<h3 id="callout">Callout</h3>

```html
<aside style="background-color:#0000000;color:#ffffff">
    <mark></mark>
</aside>
```

<h2 id="node-templates">Node Templates</h2>

Some nodes have templates. And, as the theme developer, you can customize them if the default template doesn't fit your design. To do that, add the given file to the `/templates` folder.

<h3 id="template-link-bookmark">Link Bookmark</h3>

Custom file name: `node-bookmark.twig`

Default template:

```html
<a class="bookmark" target="_blank" href="{{ data.url }}" data-url="{{ data.original_url }}">
    <div class="bookmark-details">
        <div class="bookmark-title">{{ data.title }}</div>
        <div class="bookmark-description">{{ data.description }}</div>
        <div class="bookmark-domain">{{ data.domain }}</div>
    </div>
    <div class="bookmark-thumbnail">
        <img src="{{ data.thumbnail_url }}"  alt="{{ data.title }}"/>
    </div>
</a>
```

`data` object definition:

```json
{
    "url": "https://blogs.hyvor.com",
    "original_url": "https://blogs.hyvor.com",
    "title": "Hyvor Blogs",
    "description": "A simple blogging platform",
    "domain": "blogs.hyvor.com",
    "thumbnail_url": "https://blogs.hyvor.com/thumbnail.png",
}
```

<h3 id="template-toc">Table of Contents (TOC)</h3>

Custom file name: `node-toc.twig`

Default template:

```html
{{ toc | raw }}
```

The `toc` variable is a string that contains the HTML of the TOC as nested `ul` and `li` elements.

Example: If you want to add a heading to the TOC, you can do it as follows. The `raw` filter is required to render the HTML of the TOC.

```html
<div class="toc-wrap">
    <h2>Table of Contents</h2>
    {{ toc | raw }}
</div>
```

<h2 id="light-dark">Light/Dark Modes</h2>

The blogger has the following options to choose in the Console.

- What modes are allowed?
  - Light
  - Dark
  - Both
- What is the default mode?
  - User's OS-level preference (default)
  - Light
  - Dark

It would be troublesome for you to write logic to consider all these options and find out what theme to show to the user. Therefore, we make developing light/dark easy by adding a class name to the `<html></html>` element. You can decide colors based on that class. We recommend you to use <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties" rel="nofollow">CSS Variables</a> to define colors in `colors.scss` file.

Here's an example of how to define colors for light and dark modes.

```css
.mode-light:root {
    --color-background: #ffffff;
    --color-text: #000000;
}
.mode-dark:root {
    --color-background: #000000;
    --color-text: #ffffff;
}
```

Then, in elements, use those variables. Avoid hard coding colors values!

```css
body {
    background-color: var(--color-background);
    color: var(--color-text);
}
```

That is all you have to do to support light and dark modes. We take care of showing the correct theme to the user.

<Callout type="info">
	<p>
		Under the hood, determining the color is handled by a small Javascript code injected into the
		blog in the <code>_head</code>
		<a href="/docs/themes-templates#placeholders">placeholder</a>.
	</p>
</Callout>

<h2 id="mode-toggler">Light/Dark Mode Toggler</h2>

If you support both light and dark themes, you will mostly likely have a button that allows users to toggle between color modes. In most other platforms, you have to write logic to do this manually and save preferences in local storage - but not in Hyvor Blogs!

We mentioned above that we add a small Javascript code to help you with determining light/dark modes. It also exposes a simple API to help you with toggling modes.

```html
_hb.changeColorMode(mode); // mode = os|light|dark

_hb.getColorMode() // returns light|dark
_hb.getColorModePreference() // returns light|dark|os
```

Use these global functions in the toggle buttons - We'll handle the LocalStorage.

```html
<div class="mode-toggler">
    <button class="toggle-dark" onclick="_hb.changeColorMode('dark')"><!-- DARK MODE ICON --></button>
    <button class="toggle-light" onclick="_hb.changeColorMode('light')"><!-- LIGHT MODE ICON --></button>
</div>
```

You can use a SCSS like this to show buttons on the based on the theme. This will show the light mode icon in dark mode, and dark mode button in light mode.

```css
.mode-dark {
    .toggle-dark {
        display:none;
    }
}
.mode-light {
    .toggle-light {
        display:none;
    }
}
```

<h2 id="mode-toggler-os">Light + Dark + OS Preference Toggler</h2>

Some may also want to add OS preference option to the toggler. In that case, the HTML will be similar but we have to use `mode-preference-*` classes to detect the user's preference.

- `mode-light` and `mode-dark` classes are added to the `<html>`, and they represent current **color mode**.
- `mode-preference-light`, `mode-preference-dark`, `mode-preference-os` are added to the `<html>` and they represent the current **color mode preference**.

```html
<div class="mode-toggler">
    <button class="toggle-light" onclick="_hb.changeColorMode('dark')"><!-- LIGHT MODE ICON --></button>
    <button class="toggle-dark" onclick="_hb.changeColorMode('os')"><!-- DARK MODE ICON --></button>
    <button class="toggle-os" onclick="_hb.changeColorMode('light')"><!-- OS MODE ICON --></button>
</div>
```

The following SCSS code will display the currently active mode preference button.

```css
.toggle-dark, .toggle-light, .toggle-os {
    display:none;
}
.mode-preference-light .toggle-light {
    display:inline-block;
}
.mode-preference-dark .toggle-dark {
    display:inline-block;
}
.mode-preference-os .toggle-os {
    display:inline-block;
}
```

<Callout type="info">
	<p>
		Please note that these examples are just here to explain you how it works. Feel free to design
		more creative color mode togglers ;)
	</p>
</Callout>
