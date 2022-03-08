# Templates


When a blog gets a request, first, we match its path to a Route (let's assume the route `post` for `/hello-world`). Then, we fetch required data from our database, then we call the Twig template file defined in that route (`post.twig`).

Inside this file, you can include other files or even use [inheritance](https://twig.symfony.com/doc/3.x/templates.html#template-inheritance). You can even call our [Data API](api-data) to fetch more data (More on that below)!



#### templates

This folder contains templates files. There are several types of template files.

Type | Description | Examples
---| --- |---|
**Main** | These template files are rendered directly. | `index.twig` `post.twig`
**Partial** | These templates are not rendered directly but included in main template files. They start with an underscore (`_`) | `_footer.twig`
**Route** | These templates are used to define custom routes for a blog. The file name starts with `route-`. See [custom routes](#custom-routes) below | `route-authors.twig`
**Component** | These templates are used to define new HTML structures for complex components like link previews. See [Embed: Link](#embed-link). | `component-rich-link.twig`



## Flashload

[Flashload](https://github.com/hyvor/flashload) is added to all blogs by default. Therefore, it is important to keep Flashload in mind while designing themes. Please take a minute and read the [Flashload documentation](https://github.com/hyvor/flashload#readme) to get the idea of how it works. 

Why Flashload? Browser reloads are slow. They load the same CSS/JS resources multiple times making page rendering slower. Flashload starts loading other pages even before the user clicks the link. It makes navigation smoother. It simply turns the blog into a **Single Page Application (SPA)**!

We previously learned that there's only one `styles.css` for a blog that contains all CSS of the blog. This `styles.css` should be loaded inside the `<head>` of the page. When the user navigates to another page, Flashload sends an AJAX request to that path and pre-fetches the HTML page. Then, it updates **only the `<body>` part**. (Remember, we already have all CSS loaded in the first request, so we don't want to load it again).

The simply rule is to add shared resources of the blog to `<head>`.


## Route Variables {#variables}

- The theme developer (you) creates the **theme**
- The blogger creates the content (**data**)
- HB combines the **theme** and **data** and generates the blog.

When rendering the twig templates, we send data into your template file as objects. You will use this data to generate a beautiful UI.

There are 4 main objects in HB: `Blog` , `Post` , `Tag` , and `Author`. These objects are explained in the [Data API](api-data) page.

| Variable name | Available Routes | Description |
| --- | --- | --- |
| `_blog` | (all) | A Blog object, that includes all blog-level data/settings. |
| `_config` | (all) | Theme config (`config.yaml`) as an object |
| `_scope` | (all) | a string. one of `index`, `post`, `page`, `tag`, `author`, or `search` |
| `_posts` | (all)| An array of Post objects, filtered by the [route](routes)'s filter value |
| `_featured_posts` | index | An array of Posts objects (all featured posts). |
| `_post` | post and page | A Post object |
| `_tag` | tag | A Tag object (the current tag) |
| `_author` | author | An Author object (the current author) |

Each Route gets different variables. We prefix each variable with `_` so that it won't conflict with the variables you define inside the theme files (Obviously, you shouldn't prefix `_` your variables inside the Twig template)


## Placeholders

You are required to put some placeholders in your theme to make a few things work.

| Placeholder | Scopes | Description |
| --- | --- | --- |
| `_head` | all | place before `</head>`. We automatically add SEO tags, styles.css link, and code_head set by the blogger. |
| `_foot` | all | place before `</body>`. We place the code_foot set by the blogger. |
| `_comments` | post and page | to embed the commenting system |
| `_comment_count` (optional) | post and page | to render the comment count of that page. For example, some themes have comment count at the top with a link to the comments section to encourage more comments. Only works when Hyvor Talk is connected. |
| `_newsletter` | post and page | to embed the newsletter subscription form |
| `_lang` | all | Language code of the current page. Should be placed as `<html lang="{{ _lang }}">`

Sending all placeholders (except `_lang`) through the `raw` filter is absolutely required, otherwise, the HTML code in the variables will just be escaped and printed (HB uses Twig with *automatic escaping* turned on).

```twig
{{ _head | raw }}
```


## Twig Filters & Functions

We provide two custom Twig filters.

- `data` - a function to call the Data API. See [Fetching data](#fetch-data) below.
    ```twig
    {% set posts = data(endpoint="posts", filter="author.slug=user") }
    ```

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
    
- `lang` - a filter for translations. Learn more in [languages](themes-languages).




## Fetching Data {#fetch-data}

Use the `data` function to fetch data from our [Data API](api-data).

```twig
<!-- Fetch data -->
{% set recent_posts = data(endpoint="posts", sort="published_at DESC", limit="5") %}

<!-- Render UI -->
<div id="recent-posts">
    {% for post in recent_posts %}
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


