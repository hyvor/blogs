# Templates


When a blog gets a request, first, we match its path to a Route (let's assume the route `post` for `/hello-world`). Then, we fetch required data from our database, then we call the Twig template file defined in that route (`post.twig`).


Inside this file, you can include other files or even use [inheritance](https://twig.symfony.com/doc/3.x/templates.html#template-inheritance). You can even call our [Data API](api-data) to fetch more data (More on that below)!


## Twig {#twig}

We use [Twig 3.0](https://twig.symfony.com/doc/3.x/) for templating. It is a powerful language with a plenty of in-built tags, filters, and functions. Twig also has nice, easy-to-follow documentation, which was one reason we chose Twig over other template languages. If you haven't used it ever, go through the [Twig for Template Designers](https://twig.symfony.com/doc/3.x/templates.html) page, and you will get an idea of how it works. Basically, it's HTML with superpowers.

#### templates

This folder contains templates files. There are several types of template files.

Type | Description | Examples
---| --- |---|
**Main** | These template files are rendered directly. | `index.twig` `post.twig`
**Partial** | These templates are not rendered directly but included in main template files. They start with an underscore (`_`) | `_footer.twig`
**Route** | These templates are used to define custom routes for a blog. The file name starts with `route-`. See [custom routes](#custom-routes) below | `route-authors.twig`
**Component** | These templates are used to define new HTML structures for complex components like link previews. See [Embed: Link](themes-styles#embed-link). | `component-rich-link.twig`


## Route Variables {#variables}

- The theme developer (you) creates the **theme**
- The blogger creates the content (**data**)
- HB combines the **theme** and **data** and generates the blog.

When rendering the twig templates, we send data into your template file as objects. You will use this data to generate a beautiful UI.

There are 4 main objects in HB: `Blog` , `Post` , `Tag` , and `Author`. These objects are explained in the [Data API](api-data) page.

| Variable name | Available Routes | Description                                                                                               |
| --- | --- |-----------------------------------------------------------------------------------------------------------|
| `_blog` | (all) | A Blog object, that includes all blog-level data/settings.                                                |
| `_lang` | all | A Language Object for the **current** language. Should also be placed in `<html lang="{{ _lang.code }}">` |
| `_config` | (all) | Theme config (`config.yaml`) as an object                                                                 |
| `_route` | (all) | Current [route](routes) name.                                                                             |
| `_posts` | (all)| An array of Post objects, filtered by the [route](routes)'s filter value                                  |
| `_featured_posts` | index | An array of Posts objects (all featured posts).                                                           |
| `_post` | post and page | A Post object                                                                                             |
| `_tag` | tag | A Tag object (the current tag)                                                                            |
| `_author` | author | An Author object (the current author)                                                                     |

Each Route gets different variables. We prefix each variable with `_` so that it won't conflict with the variables you define inside the theme files (Obviously, you shouldn't prefix `_` your variables inside the Twig template)

## Placeholders {#placeholders}

You are required to put some placeholders in your theme to make a few things work.

| Placeholder | Scopes | Description |
| --- | --- | --- |
| `_head` | all | place before `</head>`. We automatically add SEO tags, styles.css link, and code_head set by the blogger. |
| `_foot` | all | place before `</body>`. We place the code_foot set by the blogger. |
| `_comments` | post and page | to embed the commenting system |
| `_comment_count` (optional) | post and page | to render the comment count of that page. For example, some themes have comment count at the top with a link to the comments section to encourage more comments. Only works when Hyvor Talk is connected. |
| `_newsletter` | post and page | to embed the newsletter subscription form |

Sending all placeholders (except `_lang`) through the `template` filter is absolutely required to make them render as templates.
  
```twig
{{ _head | template }}
```

> `{{ _head | template }}` is equal to `{{ include(template_from_string(_head)) }}` in Twig. We defined the custom `template` filter to make it easier for you to write it, as it is used frequently in HB templates.

## Twig Helpers {#twig-helpers}

We provide a few custom Twig functions and filters to make writing templates easier.

### Functions {#helper-functions}

- `data` - a function to call the Data API. See [Fetching data](#fetch-data) below.
    ```twig
    {% set posts = data(endpoint="posts", filter="author.slug=user") %}
    ```
  
- `icon` - a function to get an icon.
    ```twig
    {{ icon('bootstrap', 'arrow-down', 20, 20) }}
    ```
    Function definition: `icon(iconLibrary, iconName, width, height)`
    * All icon names are lowercase, and words are separated by `-` (`arrow-down`).
    * These icon libraries are supported
      * [bootstrap](https://icons.getbootstrap.com/)
      * [fontawesome](https://fontawesome.com/icons) (Free icons only)
         * append `-regular` to regular icons (`calendar-regular`)
         * append `-solid` to solid icons (`calendar-solid`)
         * Do not append anything for brand icons (`github`)
      * [ionicons](https://ionic.io/ionicons)
      * [heroicons](https://heroicons.com/)
         * append `-solid` to solid icons (`archive-solid`)
         * append `-outline` to outline icons (`archive-outline`)
      * [octicons](https://primer.github.io/octicons)
      * [css.gg](https://css.gg/)
    > Under the hood, we use the [php-svg-icons](https://github.com/hyvor/php-svg-icons) open-source library. If you need to add more icon libraries, please send a PR there.

### Filters {#helper-filters}

- `asset_url` - a filter to link assets
    - Turns an asset filename into its absolute URL.
    - Adds last updated timestamp as a query param (to bypass browser cache on updates)
    
    ```html
    {{ 'script.js' | asset_url }}
    ```
    
    ```html
    <script src="{{ 'script.js' | asset_url }}"></script>
    
    // changes to:

    <script src="https://subdomain.hyvorblogs.io/assets/script.js?v=12931923993"></script>
    ```

- `asset` - a filter to directly print assets (only for text assets like SVGs)
    ```html
    {{ 'beauty.svg' | asset }}
    ```
  
- `pagination_page_url` - a filter to convert a page number to full URL
    ```html
    <a href="{{ _pagination.page_prev | pagination_page_url }}">Previous Page</a>
    ```
    
- `lang` - a filter for translations. Learn more in [internationalization](themes-internationalization).
- `lang_by_number` - See [conditional strings based on a number](themes-internationalization#lang-by-number)
- `language_variant_url` - See [language switcher](themes-internationalization#language-switcher)

> The difference between functions and filters can be quite confusing in Twig. Our general rule is to use functions when multiple inputs are taken (`data` and `icon`) and use filters when only a single input matters (`asset_url`, `asset`, etc.).

## Fetching Data {#fetch-data}

Use the `data` function to fetch data from our [Data API](api-data).

```twig
<!-- Fetch data -->
{% set recent_posts = data(endpoint="posts", sort="published_at DESC", limit="5") %}

<!-- Render UI -->
<div id="recent-posts">
    {% for post in recent_posts.data %}
        {% include '_recent-post-card.twig' with post  %}  
    {% endfor %}
</div>
```

Use the `endpoint` named argument to set the API endpoint. You can set all other parameters by just sending them as named arguments to the `data` Twig function (Ex: `sort="published_at DESC"`).




## Custom Routes {#custom-routes}

There are two ways to add custom routes:

* The blogger can add custom routes from the console (See [docs](routes#custom)).
* Theme developers can define custom routes by adding files named `route-{route}.twig` to the `templates` folder.

The first option is more robust, and it providers easier way to automatically set input variables `_posts` (by filtering), `_tag`, `_author`, etc so you can access them without calling the Data API. But, as a theme developer, you will need to use the second option.

For example, let's say you decide that your theme want a page to list all authors of the blog. You can add a `route-authors.twig` to the `templates` folder. If the blog gets a request to `/authors`, this template will be rendered automatically.


