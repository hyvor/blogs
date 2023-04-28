# Configuration

The purpose of configurations is to make themes easily customizable from the Console without having to touch any code. Configurations may be used to allow the blogger to turn on or off features, change colors and fonts, or even define API keys for external services.

> If you are developing a theme for yourself or a single client, you **may not** want to use configurations.  However, if you are planning to [publish](themes-publishing) your theme, adding configurations is required.

All configurations are added to `config.yaml` with their default values. There are two types of configurations.

* **HB-aware configurations** - HB is aware of these configurations, and will make decisions based on their values. You too can use their values in templates.
* **Theme configurations** - HB is unaware of these configurations. You can use them in templates for dynamic content or styles.

All configurations are sent to templates in the `_config` [route variable](themes-templates#variables).

## HB-aware Configurations {#hb-aware}

HB-aware configurations should be written in `ENGLISH_UPPER_SNAKE_CASE` in `config.yaml`.

| Configuration | Description                                                                                   | Required                                | Default        |
|---------------|-----------------------------------------------------------------------------------------------|-----------------------------------------|----------------|
| `THEME_NAME` | Name of the theme                                                                             | Only if [publishing](themes-publishing) |                |
| `THEME_VERSION` | Semantic version of the theme                                                                 | Only if publishing                      |                |
| `DEMO_URL` | Can be used to set a custom demo URL when publishing                                          | No                                      | Auto-generated |
| `POSTS_PER_PAGINATION` | Number of posts loaded initially in the `_posts` [route variable](themes-templates#variables) | No                                      | 10             

## Theme Configurations {#theme-config}

Theme configurations (colors, fonts, etc.) should be written in `english_lower_snake_case`.

### config.yaml Example {#config-yaml}

While you can use multi-nested YAML configs, we recommend to use only upto one nested level.

```yaml
THEME_NAME: hello
THEME_VERSION: 1.0.0
POSTS_PER_PAGINATION: 15

dark_theme: Yes
accent_color: 0000000
image_service:
    api_key:
    api_version: 2
```

In this example, the first 3 lines are HB-aware configurations. Others are theme configurations. You can add as many theme configurations as you need.

### config.def.yaml {#config-def-yaml}

`config.def.yaml` "describes" your **theme configurations** (There's no need to describe HB-aware configurations).

This is an example `config.def.yaml` file that explains the configurations of the previous example.

```yaml
dark_theme:
    $default: Yes
    $title: Dark theme
    $description: Turn on dark theme for this blog
    $type: checkbox
    
accent_color:
    $default: "#000000"
    $title: Accent Color
    $description: Main color of the blog
    $type: color
    
image_service:
    $title: Image Service API Details

    api_key:
		$default:
        $title: API Key
        $description: ...
        $type: text
        $options:
             max_length: 255

    api_version:
		$default: 1
        $title: API Version
        $description: ...
        $type: number
        $options:
             min: 1
             max: 2
```

> We use the `config.def.yaml` file to render the `config.yaml` file in **Console &rarr; Theme** as a UI instead of a file. Also, adding conditions in the def file (Ex: min, max) makes sure that wrong configurations are not set by the blogger.

#### Supported `$type` s

Types are equivalent to HTML `<input>` types but have some additions to support other form elements such as `<select>` out of the box.

These are the supported types:

| `$type` | Description                                                                                  |
| --- |----------------------------------------------------------------------------------------------|
| text | `<input type="text">` . Single-line text input. This is the default, if _type is not defined |
| textarea | `<textarea>`. Multi-line text input.                                                         |
| checkbox | `<input type="checkbox">` . Boolean input                                                    |
| radio | A radio group. See examples [below](#radio)                                                  |
| select | `<select>` element. See examples [below](#select)                                            |
| color | `<input type="color">`. To choose any color                                                  |
| color_palette | A color palette with only the given color values. See [below](#color-palette)                |
| date | Date picker                                                                                  |
| number | `<input type="number">` Select a number                                                      |
| range | `<input type="range">`                                                                       |

#### Supported `$options`

| `$options`    | Description                                      |
|---------------|--------------------------------------------------|
| `$max_length` | Maximum number of characters in an input         |
| `$min`        | Minimum value for an input (for number or range) |
| `$max`        | Maximum value for an input (for number or range) |
| `$step` | Range step |
| `$required`   | Set if the input is required (cannot be empty)   |


#### Radio {#radio}

Use radio input type when you have a limited number of values for a configuration. If you have more than, for example, 3 values, consider using Select instead of Radio. The difference between `radio` and `select` is that, `radio` shows all the options to the user while `select` only shows the selected value - the user has to click to see other values.

```yaml
some_key:
    $title: When to use caching
    $type: radio
    $values: 
         all: For All Posts and Pages
         posts: Only Posts
	       pages: Only Pages
```

When using `radio` , `_values` is required, which is takes `key: label` pairs. `key` is the actual value that will be saved in the `config.yaml` file. `label` is what the user will see.

#### Select {#select}

This is exactly similar to `radio`. Only the UI is different.

```yaml
some_key:
    $title: Font Type
    $type: select
    $values: 
         serif: Serif
         sans: Sans
         sans_serif: Sans-serif
```

#### Color Palette {#color-palette}

Use a YAML array to define `$values`.

```yaml
some_key:
    $title: Choose a color
    $type: color_palette
    $values: [#000000, #ffffff]
```

or

```yaml
some_key:
		$title: Choose a color
    $type: color_palette
    $values:
        - #000000
        - #ffffff
```

## Using Configurations in Templates

After defining configurations, you can use them in your templates. You can access configurations via the `_config` route variable. 

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

```twig
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