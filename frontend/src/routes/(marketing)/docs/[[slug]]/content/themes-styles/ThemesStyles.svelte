<script lang="ts">
	import { CodeBlock, Callout } from '@hyvor/design/components';
</script>

<h1>Styling Themes</h1>

<p>This folder contains SCSS files. <code>index.scss is required.</code></p>
<p>
	While developing and working with other blogging platforms/CMSs, we understood that customizing a
	theme becomes really hard when the theme developer puts all CSS in a single file. Therefore, we
	decided that we want to support "chunk-css" files to make it easy to edit for the blogger. And, we
	use <a href="https://sass-lang.com/" rel="nofollow">SCSS</a> instead of CSS to make the theme developer's
	life easier. All CSS is valid SCSS. So, if you haven't use SCSS earlier, just use CSS. SCSS just have
	some cool features like nesting rules.
</p>

<p>
	Back to “chunk-css”. let’s say you make a partial file for the blog header (<code
		>templates/_header.twig</code
	>). Then create an SCSS file to hold its CSS (<code>header.scss</code>). This pattern makes
	understanding and editing easier for the blogger. Finally, import all chunk files to
	<code>index.scss</code>
	using <code>@import</code> statements.
</p>

<CodeBlock
	code={`
    @import 'css-variables.scss';
    @import 'header.scss';
    @import 'body.scss';
    `}
	language="css"
/>

<p>
	On our side, we process <code>index.scss</code> file and generate a <code>styles.css</code>, which
	will be accessible via <ocde>/styles.css</ocde>.
	<b>That is the only CSS file of the whole blog!</b>
</p>

<Callout type="info">
	<p>
		We strongly encourage you to write CSS from scratch without using any libraries like Bootstrap.
		A blog theme is very simple and it is totally possible to manage everything on your own without
		depending on third-party libraries. If you really want to use a library, add it to assets
		instead of styles.
	</p>
</Callout>

<h2 id="fonts">Fonts</h2>

<p>
	The easiest way to load fonts is by adding <code>THEME_FONTS</code> to the
	<a href="/docs/themes-config">config</a> file.
</p>
<CodeBlock
	code={`
            THEME_FONTS: "mulish:400"
            `}
	language="yaml"
/>

<p>
	Then, you can use the font in your SCSS files. See our <a href="/docs/fonts">fonts</a> page for a in-depth
	guide.
</p>

<h2 id="advanced-nodes">Advanced Nodes</h2>

<p>
	<a href="/docs/writing">Writing</a> page describes all supported nodes. We try to use the most basic
	HTML elements to represent each node. However, there are some advanced components that require some
	attention when writing styles.
</p>

<h3 id="image">Image</h3>
<CodeBlock
	code={`
                        <figure>
                            <img src="https://exmaple.com/image.png" />
                            <figcaption>Here goes the caption</figcaption>
                        </figure>
                        `}
/>

<p>
	Note that figcaption can be empty. So, check if margins look good when figcaption is not there.
</p>

<h3 id="embed-rich">Embed</h3>
<CodeBlock
	code={`
                        <figure>
                            <div class="rich-embed">
                                {# embed HTML code goes here... #}
                            </div>
                            <figcaption>Here goes the caption</figcaption>
                        </figure>
                        `}
/>

<h3 id="embed-link">Link Bookmark</h3>
<CodeBlock
	code={`
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
                            `}
/>

<h3 id="callout">Callout</h3>
<CodeBlock
	code={`
                        <aside style="background-color:#0000000;color:#ffffff">
                            <mark></mark>
                        </aside>
                            `}
/>

<h2 id="node-templates">Node Templates</h2>

<p>
	Some nodes have templates. And, as the theme developer, you can customize them if the default
	template doesn't fit your design. To do that, add the given file to the <code>/templates</code> folder.
</p>

<h3 id="template-link-bookmark">Link Bookmark</h3>

<p>
	Custom file name: <code>node-bookmark.twig</code>
</p>

<p>Default template:</p>

<CodeBlock
	code={`
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
            `}
/>

<p>
	<code>data</code> object definition:
</p>

<CodeBlock
	code={`
                {
                    "url": "https://blogs.hyvor.com",
                    "original_url": "https://blogs.hyvor.com",
                    "title": "Hyvor Blogs",
                    "description": "A simple blogging platform",
                    "domain": "blogs.hyvor.com",
                    "thumbnail_url": "https://blogs.hyvor.com/thumbnail.png",
                }
            `}
	language="json"
/>

<h3 id="template-toc">Table of Contents (TOC)</h3>

<p>
	Custom file name: <code>node-toc.twig</code>
</p>

<p>Default template:</p>

<CodeBlock
	code={`
                {{ toc | raw }}
            `}
/>

<p>
	The <code>toc</code> variable is a string that contains the HTML of the TOC as nested
	<code>ul</code>
	and <code>li</code> elements.
</p>

<p>
	Example: If you want to add a heading to the TOC, you can do it as follows. The <code>raw</code> filter
	is required to render the HTML of the TOC.
</p>

<CodeBlock
	code={`
                <div class="toc-wrap">
                    <h2>Table of Contents</h2>
                    {{ toc | raw }}
                </div>
            `}
/>

<h2 id="light-dark">Light/Dark Modes</h2>

<p>The blogger has the following options to choose in the Console.</p>
<ul>
	<li>What modes are allowed?</li>
	<ul>
		<li>Light</li>
		<li>Dark</li>
		<li>Both</li>
	</ul>

	<li>What is the default mode?</li>
	<ul>
		<li>User's OS-level preference (default)</li>
		<li>Light</li>
		<li>Dark</li>
	</ul>
</ul>

<p>
	It would be troublesome for you to write logic to consider all these options and find out what
	theme to show to the user. Therefore, we make developing light/dark easy by adding a class name to
	the <code>{`<html></html>`}</code> element. You can decide colors based on that class. We
	recommend you to use
	<a
		href="https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties"
		rel="nofollow">CSS Variables</a
	>
	to define colors in <code>colors.scss</code> file.
</p>

<p>Here's an example of how to define colors for light and dark modes.</p>

<CodeBlock
	code={`
                .mode-light:root {
                    --color-background: #ffffff;
                    --color-text: #000000;
                }
                .mode-dark:root {
                    --color-background: #000000;
                    --color-text: #ffffff;
                }
                    `}
/>

<p>Then, in elements, use those variables. Avoid hard coding colors values!</p>

<CodeBlock
	code={`
            body {
                background-color: var(--color-background);
                color: var(--color-text);
            }
                `}
/>

<p>
	That is all you have to do to support light and dark modes. We take care of showing the correct
	theme to the user.
</p>

<Callout type="info">
	<p>
		Under the hood, determining the color is handled by a small Javascript code injected into the
		blog in the <code>_head</code>
		<a href="/docs/themes-templates#placeholders">placeholder</a>.
	</p>
</Callout>

<h2 id="mode-toggler">Light/Dark Mode Toggler</h2>

<p>
	If you support both light and dark themes, you will mostly likely have a button that allows users
	to toggle between color modes. In most other platforms, you have to write logic to do this
	manually and save preferences in local storage - but not in Hyvor Blogs!
</p>
<p>
	We mentioned above that we add a small Javascript code to help you with determining light/dark
	modes. It also exposes a simple API to help you with toggling modes.
</p>

<CodeBlock
	code={`
            _hb.changeColorMode(mode); // mode = os|light|dark

            _hb.getColorMode() // returns light|dark
            _hb.getColorModePreference() // returns light|dark|os
            `}
/>

<p>Use these global functions in the toggle buttons - We'll handle the LocalStorage.</p>

<CodeBlock
	code={`
            <div class="mode-toggler">
                <button class="toggle-dark" onclick="_hb.changeColorMode('dark')"><!-- DARK MODE ICON --></button>
                <button class="toggle-light" onclick="_hb.changeColorMode('light')"><!-- LIGHT MODE ICON --></button>
            </div>
            `}
/>

<p>
	You can use a SCSS like this to show buttons on the based on the theme. This will show the light
	mode icon in dark mode, and dark mode button in light mode.
</p>

<CodeBlock
	code={`
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
                `}
/>

<h2 id="mode-toggler-os">Light + Dark + OS Preference Toggler</h2>
<p>
	Some may also want to add OS preference option to the toggler. In that case, the HTML will be
	similar but we have to use <code>mode-preference-*</code> classes to detect the user's preference.
</p>

<ul>
	<li>
		<code>mode-light</code> and <code>mode-dark</code> classes are added to the
		<code>{`<html>`}</code>, and they represent current <b>color mode</b>.
	</li>
	<li>
		<code>mode-preference-light</code>, <code>mode-preference-dark</code>,
		<code>mode-preference-os</code>
		are added to the <code>{`<html>`}</code> and they represent the current
		<b>color mode preference</b>.
	</li>
</ul>

<CodeBlock
	code={`
                <div class="mode-toggler">
                    <button class="toggle-light" onclick="_hb.changeColorMode('dark')"><!-- LIGHT MODE ICON --></button>
                    <button class="toggle-dark" onclick="_hb.changeColorMode('os')"><!-- DARK MODE ICON --></button>
                    <button class="toggle-os" onclick="_hb.changeColorMode('light')"><!-- OS MODE ICON --></button>
                </div>
                `}
/>

<p>The following SCSS code will display the currently active mode preference button.</p>

<CodeBlock
	code={`
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
                    `}
/>

<Callout type="info">
	<p>
		Please note that these examples are just here to explain you how it works. Feel free to design
		more creative color mode togglers ;)
	</p>
</Callout>
