<script lang="ts">
	import { Callout, CodeBlock, Table, TableRow } from '@hyvor/design/components';
</script>

<h1 id="configuration">Configuration</h1>

<p>
	The purpose of configurations is to make themes customizable to some extent without having to
	change the theme code. Configurations may be used to allow the blogger to turn on or off
	features, change colors and fonts, or even define API keys for external services.
</p>

<Callout type="info">
	<p>
		If you are developing a theme for yourself or a single client, you <b>may not</b> want to
		use configurations. However, if you are planning to
		<a href="/docs/themes-publishing">publish</a> your theme, adding configurations is required.
	</p>
</Callout>

<p>
	All configurations are added to <code>config.yaml</code> with their default values. There are two
	types of configurations.
</p>

<ul>
	<li>
		<b>HB-aware configurations</b> - HB is aware of these configurations, and will make decisions
		based on their values. You too can use their values in templates.
	</li>
	<li>
		<b>Theme configurations</b> - HB is unaware of these configurations. You can use them in templates
		for dynamic content or styles.
	</li>
</ul>

<p>
	All configurations are accessible in templates from the <code>_config</code>
	<a href="/docs/themes-templates#variables">route variable</a>.
</p>

<h2 id="hb-config">HB-aware Configurations</h2>

<p>
	HB-aware configurations should be written in <code>ENGLISH_UPPER_SNAKE_CASE</code> in
	<code>config.yaml</code>.
</p>

<ul>
	<li><code>THEME_NAME</code> - Name of the theme</li>
	<ul>
		<li>Required: Only if <a href="/docs/themes-publishing">publishing</a></li>
	</ul>

	<li><code>THEME_VERSION</code> - Semantic version of the theme</li>
	<ul>
		<li>Required: Only if publishing</li>
	</ul>

	<li>
		<code>THEME_FONTS</code> - Fonts to load in the blog. See <a href="/docs/fonts">fonts</a>.
	</li>
	<ul>
		<li>Required: Only if publishing</li>
	</ul>

	<li><code>DEMO_URL</code> - Can be used to set a custom demo URL when publishing</li>
	<ul>
		<li>Required: No</li>
		<li>Default Value: Auto-generated</li>
	</ul>

	<li>
		<code>POSTS_PER_PAGINATION</code> - Number of posts loaded initially in the
		<code>_posts</code> <a href="/docs/themes-templates#variables">route variable</a>
	</li>
	<ul>
		<li>Required: No</li>
		<li>Default Value: 10</li>
	</ul>
</ul>

<h2 id="theme-config">Theme Configurations</h2>
<p>
	Theme configurations (colors, fonts, etc.) should be written in <code
		>english_lower_snake_case</code
	>.
</p>

<h3 id="config-yaml">config.yaml Example</h3>
<p>
	While you can use multi-nested YAML configs, we recommend to use only up to one or two nested
	level.
</p>

<CodeBlock
	language="yaml"
	code={`
THEME_NAME: hello
THEME_VERSION: 1.0.0
THEME_FONTS: "mulish:400,700"
POSTS_PER_PAGINATION: 15

dark_theme: Yes
accent_color: 0000000
image_service:
    api_key:
    api_version: 2
`}
/>

<p>
	In this example, the first 3 lines are HB-aware configurations. Others are theme configurations.
	You can add as many theme configurations as you need.
</p>

<h3 id="config-def">Config Definitions</h3>

<p>
	<code>config.def.yaml</code> "describes" your <b>theme configurations</b>. This helps the
	blogger to understand what each configuration does. It also helps to render the
	<code>config.yaml</code>
	file in <b>Console → Theme</b> as a UI instead of a file.
</p>
<Callout type="info">
	<p>Test your config definitions at <a href="/config">blogs.hyvor.com/config</a>.</p>
</Callout>

<p>
	This is an example <code>config.def.yaml</code> file that explains the configurations of the previous
	example.
</p>

<CodeBlock
	language="yaml"
	code={`
dark_theme:
    $name: Dark theme
    $description: Turn on dark theme for this blog
    $type: checkbox

accent_color:
    $name: Accent Color
    $description: Main color of the blog
    $type: color

image_service:
    $name: Image Service API Details

    api_key:
        $name: API Key
        $description: ...
        $type: text
        $maxlength: 255

    api_version:
        $name: API Version
        $description: ...
        $type: number
        $min: 1
        $max: 2
`}
/>

<Callout type="info">
	<p>
		We use the <code>config.def.yaml</code> file to render the <code>config.yaml</code> file in
		<code>Console → Theme</code> as a UI instead of a file. Also, adding conditions in the def file
		(Ex: min, max) makes sure that wrong configurations are not set by the blogger.
	</p>
</Callout>

<h4 id="configuration-definitions">Configuration Definitions</h4>

<p>These are the supported definitions for theme configurations:</p>

<ul>
	<li>
		<code>$type</code> - Type of the configuration. See
		<a href="/docs/themes-config#supported-types">Supported <code>$type</code>s</a>.
	</li>
	<li><code>$name</code> - Name of the configuration.</li>
	<li><code>$description</code> - Description of the configuration.</li>
	<li><code>$minlength</code> - Minimum number of characters in an input.</li>
	<li><code>$maxlength</code> - Maximum number of characters in an input.</li>
	<li><code>$min</code> - Minimum value for a number.</li>
	<li><code>$max</code> - Maximum value for a number.</li>
</ul>

<h4 id="supported-types">Supported <code>$type</code>s</h4>
<p>These are the supported types for theme configurations:</p>

<Table columns="2fr 3fr">
	<TableRow head>
		<div><code>$type</code></div>
		<div>Description</div>
	</TableRow>

	<TableRow>
		<div><code>none</code></div>
		<div>No input. This is useful for configurations that are not editable by the blogger</div>
	</TableRow>

	<TableRow>
		<div><code>text</code></div>
		<div>Single-line text input. This is the default, if <code>$type</code> is not defined</div>
	</TableRow>

	<TableRow>
		<div><code>textarea</code></div>
		<div>Multi-line text input</div>
	</TableRow>

	<TableRow>
		<div><code>number</code></div>
		<div>Select a number</div>
	</TableRow>

	<TableRow>
		<div><code>checkbox</code></div>
		<div>Checkbox (boolean value)</div>
	</TableRow>

	<TableRow>
		<div><code>radio</code></div>
		<div>
			Select one of several options. See examples <a href="/docs/themes-config#radio">below</a
			>
		</div>
	</TableRow>

	<TableRow>
		<div><code>color</code></div>
		<div>Select a color</div>
	</TableRow>
</Table>

<h4 id="radio">Radio Example</h4>

<p>
	You can set radio options in <code>$options</code>, which is a <code>key: label</code> pair
	list. <code>key</code> is the actual value that will be saved in the <code>config.yaml</code>
	file. <code>label</code> is what the user will see.
</p>

<CodeBlock
	language="yaml"
	code={`
some_key:
    $title: When to use caching
    $type: radio
    $options: 
        all: For All Posts and Pages
        posts: Only Posts
        pages: Only Pages
`}
/>

<h2 id="config-usage">Using Configurations in Templates</h2>
<p>
	After defining configurations, you can use them in your templates. You can access configurations
	via the _config route variable.
</p>

<p>Example: Configurable CSS variables.</p>
<p><code>config.yaml</code>:</p>

<CodeBlock
	language="yaml"
	code={`
colors:
  accent: "#896c6b"

font:
  size: 16
  family: "Nunito, sans-serif"

line_height: 24

box:
  radius: 20
  shadow: "0 0 30px rgba(0,0,0,0.05)"
`}
/>

<p>Then, use configs in your templates.</p>

<CodeBlock
	language="html"
	code={`
<style>
    :root {
        --color-accent: {{ _config.colors.accent }};
        --font-size: {{ _config.font.size }}px;
        --font-family: {{ _config.font.family }};
        --line-height: {{ _config.line_height }}px;
        --box-radius: {{ _config.box.radius }}px;
        --box-shadow: {{ _config.box.shadow }};
    }
</style>
`}
/>
