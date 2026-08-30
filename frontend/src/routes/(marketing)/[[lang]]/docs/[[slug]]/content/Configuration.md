<script lang="ts">
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Configuration

The purpose of configurations is to make themes customizable to some extent without having to
change the theme code. Configurations may be used to allow the blogger to turn on or off features,
change colors and fonts, or even define API keys for external services.

<Callout type="info">
	<p>
		If you are developing a theme for yourself or a single client, you <b>may not</b> want to use
		configurations. However, if you are planning to
		<a href="/docs/themes-publishing">publish</a> your theme, adding configurations is required.
	</p>
</Callout>

All configurations are added to `config.yaml` with their default values. There are two types
of configurations.

- **HB-aware configurations** - HB is aware of these configurations, and will make decisions based on their values. You too can use their values in templates.
- **Theme configurations** - HB is unaware of these configurations. You can use them in templates for dynamic content or styles.

All configurations are accessible in templates from the `_config`
[route variable](/docs/themes-templates#variables).

<h2 id="hb-config">HB-aware Configurations</h2>

HB-aware configurations should be written in `ENGLISH_UPPER_SNAKE_CASE` in
`config.yaml`.

- `THEME_NAME` - Name of the theme
  - Required: Only if [publishing](/docs/themes-publishing)
- `THEME_VERSION` - Semantic version of the theme
  - Required: Only if publishing
- `THEME_FONTS` - Fonts to load in the blog. See [fonts](/docs/fonts).
  - Required: Only if publishing
- `DEMO_URL` - Can be used to set a custom demo URL when publishing
  - Required: No
  - Default Value: Auto-generated
- `POSTS_PER_PAGINATION` - Number of posts loaded initially in the `_posts` [route variable](/docs/themes-templates#variables)
  - Required: No
  - Default Value: 10

<h2 id="theme-config">Theme Configurations</h2>

Theme configurations (colors, fonts, etc.) should be written in `english_lower_snake_case`.

<h3 id="config-yaml">config.yaml Example</h3>

While you can use multi-nested YAML configs, we recommend to use only up to one or two nested
level.

```yaml
THEME_NAME: hello
THEME_VERSION: 1.0.0
THEME_FONTS: "mulish:400,700"
POSTS_PER_PAGINATION: 15

dark_theme: Yes
accent_color: 0000000
image_service:
    api_key:
    api_version: 2
```

In this example, the first 3 lines are HB-aware configurations. Others are theme configurations.
You can add as many theme configurations as you need.

<h3 id="config-def">Config Definitions</h3>

`config.def.yaml` "describes" your **theme configurations**. This helps the blogger
to understand what each configuration does. It also helps to render the
`config.yaml`
file in **Console → Theme** as a UI instead of a file.

<Callout type="info">
	<p>Test your config definitions at <a href="/config">blogs.hyvor.com/config</a>.</p>
</Callout>

This is an example `config.def.yaml` file that explains the configurations of the previous
example.

```yaml
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
```

<Callout type="info">
	<p>
		We use the <code>config.def.yaml</code> file to render the <code>config.yaml</code> file in
		<code>Console → Theme</code> as a UI instead of a file. Also, adding conditions in the def file (Ex:
		min, max) makes sure that wrong configurations are not set by the blogger.
	</p>
</Callout>

<h4 id="configuration-definitions">Configuration Definitions</h4>

These are the supported definitions for theme configurations:

- `$type` - Type of the configuration. See [Supported `$type`s](/docs/themes-config#supported-types).
- `$name` - Name of the configuration.
- `$description` - Description of the configuration.
- `$minlength` - Minimum number of characters in an input.
- `$maxlength` - Maximum number of characters in an input.
- `$min` - Minimum value for a number.
- `$max` - Maximum value for a number.

<h4 id="supported-types">Supported <code>$type</code>s</h4>

These are the supported types for theme configurations:

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
			Select one of several options. See examples <a href="/docs/themes-config#radio">below</a>
		</div>
	</TableRow>

	<TableRow>
		<div><code>color</code></div>
		<div>Select a color</div>
	</TableRow>
</Table>

<h4 id="radio">Radio Example</h4>

You can set radio options in `$options`, which is a `key: label` pair list.
`key`
is the actual value that will be saved in the `config.yaml`
file. `label` is what the user will see.

```yaml
some_key:
    $title: When to use caching
    $type: radio
    $options: 
        all: For All Posts and Pages
        posts: Only Posts
        pages: Only Pages
```

<h2 id="config-usage">Using Configurations in Templates</h2>

After defining configurations, you can use them in your templates. You can access configurations
via the _config route variable.

Example: Configurable CSS variables.

`config.yaml`:

```yaml
colors:
  accent: "#896c6b"

font:
  size: 16
  family: "Nunito, sans-serif"

line_height: 24

box:
  radius: 20
  shadow: "0 0 30px rgba(0,0,0,0.05)"
```

Then, use configs in your templates.

```html
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
```
