# Configuration

The purpose of configurations is to make themes customizable to some extent without having to change the theme code. Configurations may be used to allow the blogger to turn on or off features, change colors and fonts, or even define API keys for external services.

> If you are developing a theme for yourself or a single client, you **may not** want to use configurations.  However, if you are planning to [publish](themes-publishing) your theme, adding configurations is required.

All configurations are added to `config.yaml` with their default values. There are two types of configurations.

* **HB-aware configurations** - HB is aware of these configurations, and will make decisions based on their values. You too can use their values in templates.
* **Theme configurations** - HB is unaware of these configurations. You can use them in templates for dynamic content or styles.

All configurations are accessible in templates from the `_config` [route variable](themes-templates#variables).

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

While you can use multi-nested YAML configs, we recommend to use only up to one or two nested level.

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

### Config Definitions {#config-def}

`config.def.yaml` "describes" your **theme configurations**. This helps the blogger to understand what each configuration does. It also helps to render the `config.yaml` file in **Console &rarr; Theme** as a UI instead of a file.

> Test your config definitions at [blogs.hyvor.com/config](https://blogs.hyvor.com/config)

This is an example `config.def.yaml` file that explains the configurations of the previous example.

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

> We use the `config.def.yaml` file to render the `config.yaml` file in **Console &rarr; Theme** as a UI instead of a file. Also, adding conditions in the def file (Ex: min, max) makes sure that wrong configurations are not set by the blogger.

#### Configuration Definitions

These are the supported definitions for theme configurations:

* `$type` - Type of the configuration. See [Supported `$type`s](#supported-types).
* `$name` - Name of the configuration.
* `$description` - Description of the configuration.
* `$minlength` - Minimum number of characters in an input.
* `$maxlength` - Maximum number of characters in an input.
* `$min` - Minimum value for a number.
* `$max` - Maximum value for a number.

#### Supported `$type` s {#supported-types}

These are the supported types for theme configurations:

| `$type` | Description                                                                                  |
| --- |----------------------------------------------------------------------------------------------|
| text | Single-line text input. This is the default, if `$type` is not defined |
| textarea | Multi-line text input.                                                         |
| number | Select a number                                                      |
| checkbox | Checkbox (boolean value)                                                    |
| radio | Select one of several options. See examples [below](#radio)                                                  |
| color | Select a color                                             |


#### Radio Example {#radio}

You can set radio options in `$options`, which is a `key: label` pair list. `key` is the actual value that will be saved in the `config.yaml` file. `label` is what the user will see.

```yaml
some_key:
    $title: When to use caching
    $type: radio
    $options: 
         all: For All Posts and Pages
         posts: Only Posts
	       pages: Only Pages
```

<!-- #### Select {#select}

This is exactly similar to `radio`. Only the UI is different.

```yaml
some_key:
    $title: Font Type
    $type: select
    $values: 
         serif: Serif
         sans: Sans
         sans_serif: Sans-serif
``` -->

## Using Configurations in Templates

After defining configurations, you can use them in your templates. You can access configurations via the `_config` [route variable](themes-templates#variables). 

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