# Theme Development Overview

In this page, you will learn basics of Hyvor Blogs templates. how to create a theme in Hyvor Blogs. Before we get started, let's make sure you understand these terms:

- Theme Developer - the person who develops a theme (must be you!)
- Blogger - the person who owns the blog, and can edit your theme from the console.
- Subdomain - subdomain part of `{subdomain}.hyvorblogs.io`  which is given to the blogger
- Rendering - combining a theme file with data and returning HTML output
- Route - routes of the blog that determines how to render a page or what output to return for a specific URL path.
- HB - Hyvor Blogs


## Twig {#twig}

We use [Twig 3.0](https://twig.symfony.com/doc/3.x/) for templating. It is a powerful language with a plenty of in-built tags, filters, and functions. Twig also has nice, easy-to-follow documentation, which was one reason we chose Twig over other template languages. If you haven't used it ever, go through the [Twig for Template Designers](https://twig.symfony.com/doc/3.x/templates.html) page, and you will get an idea of how it works. Basically, it's HTML with superpowers.


## Routes {#routes}

Let's say a blog gets a request to the URL path `/hello-world`. We use Routes to determine what output to return. By default, `/hello-world` can usually be a `post` or `page` route. If so, we check our database if a post with the `/hello-world` slug exists in our database, and then render with `post.twig` or `page.twig` with its data, and send back the output to the user.

So, before we getting started with theme development, it is important to fully understand how routes work. See our [Routes](routes) guide and come back here to continue.

## Development

Most platforms support local theme development, however, Hyvor Blogs themes are developed online.

1. To start theme development, create a development blog at [/console/new/dev](https://blogs.hyvor.com/console/new/dev). You will get a subdomain that starts with `dev-`. We recommend you to use `dev-{your_theme_name}` so that you will know what this blog was created. Development blogs are free of charge. 5 different IP addresses can visit dev blogs each day, and caching is disabled.
2. Then, go to the Theme section and start developing. You will see empty files, where you have to write Twig/SCSS code to build the theme.

To test changes, you can visit your blog’s online URL.

> We know this is not the perfect set up for a developer, as it doesn't provide all the convenient features like Git versioning and hot reloading. We have long-term plans to create a cli tool to allow you to develop themes locally.

## Caching

Another important behavior of HB is that we use caching EXTENSIVELY. We use a technique called **first-request-caching**.

- Someone requests `/hello-world` path of a blog.
- We don't have any cached output for this path. We fetch data from our database, combine it with the template, and generate the HTML output, and send the response back to the user. Behind the scenes, we save the generated HTML output in Fastly's Edge Cache.
- When someone else requests the same path, the HTML output is directly sent from the nearest Fastly Edge servers. The request never even reach our servers.

When using a cache, clearing cache is the most important thing. We have to make sure outdated content is not delivered when something changes. Here are the events that we clear cache for each scope.

- whenever whatever data is changed in the blog
- whenever the theme is edited
- on January 1st

And,

- `/search` and `/p/{hash}` (preview pages) routes are always dynamic, never cached.

> ⚠️    
> Caching makes the blog super fast. However, it puts some limitations to theme development. You can't render dynamic data like "current date" using Twig. Due to cache, users may see an old date. If absolutely required, you have to use Javascript to render dynamic content inside user's browser. However, displaying the "publish date" of a post works fine because we clear cache whenever the post is updated. Also, displaying the current year will work, because we will make sure to clear the cache on the 1st of January.

## Folder Structure

There are four folders in a HB theme folder. Nested folders are **not supported**.

```plain
/
    /templates
    /styles
    /assets
    /lang
    config.yaml
```

* **templates**: All Twig template files go here. See [templates](themes-templates).
* **styles**: All chunk SCSS files go here. See [styles](themes-styles).
* **assets**: You may add SVGs, PNGs, or Javascript files here. All files in this directory are publicly accessible via `/assets/{file_name}`. Any file type is supported.
* **lang**: All language files go here. See [languages](themes-languages).

The root folder contains config files. See [config](themes-config).