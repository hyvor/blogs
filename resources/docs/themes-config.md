# Config

When designing themes that others can use, you have to add options to customize some basic settings of the theme. For theme configurations, there are two files in the root directory: `config.yaml` and `config.def.yaml`. 

### config.yaml

This is the configuration file of the blog. However, this is hidden in the UI. We convert YAML into a beautiful UI using your `config.def.yaml` file.

```yaml
dark_theme: Yes
accent_color: 0000000
image_service:
    api_key:
    api_version: 2
```

### config.def.yaml

This is the definition file that explains what each key is expecting as its value.

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
				$default: ~
        $title: API Key
        $description: ...
        $type: text
        $options:
             maxlength: 255

    api_version:
				$default: 1
        $title: API Version
        $description: ...
        $type: number
        $options:
             min: 1
             max: 2
```

> `config.def.yaml` files are only visible in development blogs. Bloggers do not see it. Neither can they edit it.

#### Supported `$type` s

Types are equivalent to HTML `<input>` types but has some additions to support other form elements, such as `<select>` out of the box.

These are the supported types

| $type | Description |
| --- | --- |
| text | `<input type="text">` . Single-line text input. This is the default, if _type is not defined |
| textarea | `<textarea>`. Multi-line text input. |
| checkbox | `<input type="checkbox">` . Boolean input |
| radio | A radio group. See examples below for usage. |
| select | `<select>` element. See examples below for usage. |
| color | `<input type="color">`. To choose any color |
| color_palette | A color palette with only the given color values. |
| date | Date picker |
| number | `<input type="number">` Select a number |
| range | `<input type="range">` |

#### Supported `$options`

| Options | Description |
| --- | --- |
| maxlength | Maximum number of characters in an input  |
| min | Minimum value for an input (usually for number) |
| max | Maximum value for an input (usually for number) |
| required | Set if the input is required |

#### Examples

**Radio**

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

**Select**

This is exactly similar to `radio`. Only the UI is different.

```yaml
some_key:
    $title: When to use caching
    $type: select
    $values: 
         all: For All Posts and Pages
         posts: Only Posts
	       pages: Only Pages
```

**Color Palette**

Use an YAML array to define `$values`.

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