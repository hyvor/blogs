# Overview

In this page, you will learn how to create a theme in Hyvor Blogs. Before we get started, let's make sure what we mean by each special word used in this document.

- Theme Developer - the person who develops a theme (must be you!)
- Blogger - the person who owns the blog, and can edit your theme from the console.
- Subdomain - subdomain part of `{subdomain}.hyvorblogs.io`  which is given to the blogger
- Rendering - combining a theme file with data and returning HTML output
- Route - routes of the blog that determines how to render a page or what output to return for a specific URL path.
- HB - Hyvor Blogs

## Routes {#routes}

Let's say a blog gets request to the URL path `/hello-world`. We use Routes to determine what output to return. By default, `/hello-world` can usually be a `post` or `page` route. So, we check our database if a post with the `/hello-world` slug exists in our database, and then render with `post.twig` or `page.twig` with its data, and send back the output to the user.

So, before we getting started with theme development, it is important to fully understand how routes work. See our [Routes](routes) guide and come back here to continue.

As you may have noticed, Routes are a blog-level setting. The blogger sets this. However, some themes may want to use custom routes. We will look into that at the end. 

## Twig {#twig}

We use [Twig 3.0](https://twig.symfony.com/doc/3.x/) for templating. It is a powerful language with a plenty of in-built tags, filters, and functions. Twig also has nice, easy-to-follow documentation, which was one reason we chose Twig over other template languages. If you haven't used it ever, go through the Twig for [Template Designers](https://twig.symfony.com/doc/3.x/templates.html) page, and you will get an idea of how it works. Basically, it's HTML with superpowers like functions and including other files.

When a blog gets a request, first, we match its path to a Route (let's assume `post` for `/hello-world`), then fetch required data from our database, then we call the Twig template file that you created (`post.twig`). Inside this file, you can include other files or even use [inheritance](https://twig.symfony.com/doc/3.x/templates.html#template-inheritance). You can even call our [Data API](api-data) to fetch more data (More on that below)!

## Development

Developing themes are quite different from other platforms. Most platforms support local theme development, however, Hyvor Blogs themes are developed online. This is a different experience for those who usually work offline on a local filesystem.

1. To start theme development, create a development blog at [/console/new/dev](https://blogs.hyvor.com/console/new/dev). You will get a subdomain that starts with `dev-`. We recommend you to use `dev-{your_theme_name}` so that you will know what this blog was created. Development blogs are free of charge. 5 different IP addresses can visit dev blogs each day, and caching is disabled.
2. Then, go to the Theme section and start developing. You will see empty files, where you have to write Twig/SCSS code to build the theme.

To test changes, you can visit your blog’s online URL.

> We know this is not the perfect set up for a developer, as it doesn't provide all the convenient features like Git versioning and hot reloading. We have long-term plans to create a cli tool later to allow you to develop themes offline.

## Designing

Hyvor Blogs is opinionated. So, we have some rules and limitations to make sure your theme is structured, simple, fast, and secure.

### 1. Folder Structure

There are four folders in a HB theme folder. Nested folders are **not supported**.

```plain
/
    /templates
    /styles
    /assets
    /lang
    config.yaml
```

#### templates

This folder contains templates files. There are several types of template files.

Type | Description | Examples
---| --- |---|
**Main** | These template files are rendered directly. | `index.twig` `post.twig`
**Partial** | These templates are not rendered directly but included in main template files. They start with an underscore (`_`) | `_footer.twig`
**Route** | These templates are used to define custom routes for a blog. The file name starts with `route-`. See [custom routes](#custom-routes) below | `route-authors.twig`
**Component** | These templates are used to define new HTML structures for complex components like link previews. See [Embed: Link](#embed-link). | `component-rich-link.twig`

#### styles

This folder contains SCSS files. `index.scss` is required. 

While developing and working with other blogging platforms/CMSs, we understood that customizing a theme becomes really hard when the theme developer puts all CSS in a single file. Therefore, we decided that we want to support "chunk-css" files to make it easy to edit for the blogger. And, we use [SCSS](https://sass-lang.com/) instead of CSS to make the theme developer's life easier. All CSS is valid SCSS. So, if you haven’t use SCSS earlier, just use CSS. SCSS just have some cool features like nesting rules. 

Back to “chunk-css”. let’s say you make a partial file for the blog header (`templates/_header.twig`). Then create an SCSS file to hold its CSS (`header.scss`). This pattern makes understanding and editing easier for the blogger. Finally, import all chunk files to `index.scss` using `@import` statements.

```scss
@import 'css-variables.scss';
@import 'header.scss';
@import 'body.scss';
```

On our side, we process `index.scss` file and generate a `styles.css`, which will be accessible via `/styles.css`. **That is the only CSS file of the whole blog**!

And, don't worry about using vendor prefixes like `-webkit-`. We auto-prefix the `styles.css` file before sending it to the user.

> We strongly encourage you to write CSS from scratch without using any libraries like Bootstrap. A blog theme is very simple and it is totally possible to manage everything on your own without depending on third-party libraries. If you really want to use a library, add it to assets instead of styles.

#### assets

This folder contains asset files. Usually, you may use SVGs, PNGs, or Javascript files. All files in this directory are publicly accessible via `assets/{file_name}`. Any file type is supported.

#### lang

This folder contains language `.yaml` files. `en.yaml` is the default and is required. Language codes should be **[ISO 639-1 Codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).** Here's a sample lang file.

```yaml
published: Published
postsCount: "{number} Posts"
```

See [languages](#languages) below for more details. 

#### root

There are two required files in the root (without a folder): `config.yaml` and `config.def.yaml`. See [config](#themes-config) below for more details on writing configuration files.
    

## Flashload

[Flashload](https://github.com/hyvor/flashload) is added to all blogs by default. Therefore, it is important to keep Flashload in mind while designing themes. Please take a minute and read the [Flashload documentation](https://github.com/hyvor/flashload#readme) to get the idea of how it works. 

Why Flashload? Browser reloads are slow. They load the same CSS/JS resources multiple times making page rendering slower. Flashload starts loading other pages even before the user clicks the link. It makes navigation smoother. It simply turns the blog into a **Single Page Application (SPA)**!

We previously learned that there's only one `styles.css` for a blog that contains all CSS of the blog. This `styles.css` should be loaded inside the `<head>` of the page. When the user navigates to another page, Flashload sends an AJAX request to that path and pre-fetches the HTML page. Then, it updates **only the `<body>` part**. (Remember, we already have all CSS loaded in the first request, so we don't want to load it again).

The simply rule is to add shared resources of the blog to `<head>`.

## Caching

Another important behavior of HB is that we use caching EXTENSIVELY. We use a technique called **first-request-caching (FRC).**

- Someone requests `/hello-world` path of a blog.
- We don't have any cached output for this path. We fetch data from our database, combine it with the template, and generate the HTML output, and send the response back to the user. Behind the scenes, we save the generated HTML output in Fastly's Edge Cache.
- When someone else requests the same path, the HTML output is directly sent from the nearest Fastly Edge servers. The request never even reach our servers.

When using a cache, clearing cache is the most important thing. We have to make sure outdated content is not delivered when something changes. Here are the events that we clear cache for each scope.

- whenever whatever data is changed in the blog
- whenever the theme is edited
- on January 1st

Some other notes:

- `/search` is always dynamic, never cached.

> ⚠️    
> Caching makes the blog super fast. However, it puts some limitations to theme development. You can't render dynamic data like "current date" using Twig. Due to cache, users may see an old date. If absolutely required, you have to use Javascript to render dynamic content inside user's browser. However, displaying the "publish date" of a post works fine because we clear cache whenever the post is updated. Also, displaying the current year will work, because we will make sure to clear the cache on the 1st of January.

## Scope Variables {#variables}

- The theme developer (you) creates the **theme**
- The blogger creates the content (**data**)
- HB combines the **theme** and **data** and generates the blog.

When rendering the twig templates, we send data into your template file as objects. You will use this data to generate a beautiful UI.

There are 4 main objects in HB: `Blog` , `Post` , `Tag` , and `Author`. These objects are explained in the [Data API](api-data) page.

| Variable name | Available Scopes | Description |
| --- | --- | --- |
| `_blog` | (all) | A Blog object, that includes all blog-level data/settings. |
| `_config` | (all) | Theme config (`config.yaml`) as an object |
| `_scope` | (all) | a string. one of `index`, `post`, `page`, `tag`, `author`, or `search` |
| `_posts` | (all)| An array of Post objects, filtered by the [route](routes)'s filter value |
| `_featured_posts` | index | An array of Posts objects (all featured posts). |
| `_post` | post and page | A Post object |
| `_tag` | tag | A Tag object (the current tag) |
| `_author` | author | An Author object (the current author) |

Each Scope gets different variables. We prefix each variable with `_` so that it won't conflict with the variables you define inside the theme files (Obviously, you shouldn't prefix `_` your variables inside the Twig template)



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

`.env` is the only file in the root of your template. It should contain all the configurations of the theme. There are two required configurations.

- `NAME` - The name of your theme.
- `VERSION` -  [Semantic Version](https://semver.org/)

There's one optional configuration that HB understands.

- `POSTS_PER_PAGE` - The number of posts sent into the theme in the `@posts` variable in listing pages.

All other configurations are custom.

<aside>
💡 Always use descriptive names for all configurations to make it easy for the blogger to change them.

</aside>

```json
NAME="Theme Name"
VERSION="1.0.0"

POSTS_PER_PAGE=10
LANG=en
CACHE_PURGING=single|listing|blog

// all other theme configurations
THEME_COLOR=#678392
HAS_DARK_THEME=1
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
    
- `lang` - a filter for translations.

## Languages

Your templates should only contain code. All strings (which are displayed to the end-user) should be added separately inside the lang folder. This makes translations possible.

`en.env` is required. The `.env` file contains key-value pairs. The key is used in your template access the string.

```html
welcome="Welcome to our blog"
usersCount="* users"
byAuthor="by {authorName}"
```

In Twig, use the `lang` filter

```html
<h1>
	{{ 'welcome' | lang }}
</h1>

<p>{{ 'byAuthor' | lang(authorName=@author.name) }}</p>

<p>{{ 'usersCount' | lang(2)</span>
```

As you can see there are two placeholders types:

- `*`
    - Use if the string only has one input, in most cases, a number
- `named`
    - example: `{authorName}`
    - Use if the string has more inputs or if the inputs are data that can be described in name.
    - You can have multiple named placeholders in the string.
    - In the lang function, use "[named arguments](https://twig.symfony.com/doc/3.x/templates.html#named-arguments)" to fill placeholders with real data.

Let's say user changes his site's language to French (`fr`). Then, we check if a `fr.env` is available in the `lang` folder. If not, we have no clue, we'll just show English strings. However, anyone can easily translate the strings inside a `fr.env` file (even someone without technical knowledge can do that).

So, here's how an `fr` version of the above file will look like.

```html
welcome="Bienvenue sur notre blog"
usersCount="* utilisateurs"
byAuthor="par {authorName}"
```

Keys don't change, only the string.


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

## Advanced Nodes {#advanced-nodes}

"[Using the editor](editor)" page describes all supported nodes. We try to use the most basic HTML elements to represent each node. However, there are some advanced components that require some attention when writing styles.

#### Image {#image}

```twig
<figure>
    <img src="https://exmaple.com/image.png" />
    <figcaption>Here goes the caption</figcaption>
</figure>
```
Note that figcaption can be empty. So, check if margins look good when figcaption is not there.

#### Embed: Rich {#embed-rich}

```twig
<figure>
    <div class="rich-embed">
        {# embed HTML code goes here... #}
    </div>
    <figcaption>Here goes the caption</figcaption>
</figure>
```

#### Embed: Link {#embed-link}

```twig
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

**TIP:** It is possible to change this HTML structure, by adding a `component-rich-link.twig` to `/templates` folder. The `data` object is as follows.
```json
{
    "url": "https://blogs.hyvor.com",
    "title": "Hyvor Blogs",
    "description": "A simple blogging platform",
    "domain": "blogs.hyvor.com",
    "thumbnail": "https://blogs.hyvor.com/thumbnail.png",
    "icon": "https://blogs.hyvor.com/icon.png",
    "site": "Hyvor Blogs",
}
```

#### Callout {#callout}

```twig
<aside style="background-color:#0000000;color:#ffffff">
    <mark></mark>
</aside>
```