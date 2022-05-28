# Theme Development Overview

Hyvor Blogs themes are fully customizable. If you have some experience with HTML, CSS, and Javascript, you can easily build your own theme from scratch. This page is an overview to help you get started. All official themes are in the [hyvor-blogs-themes](https://github.com/hyvor/hyvor-blogs-themes) repository. Feel free to review the source code of the other themes.

Here's some of commonly used terms in this documentation:

- **Theme Developer** - the person who develops a theme (must be you!).
- **Blogger** - The person who owns the blog. They can install the theme you create and edit it through the console.
- **Subdomain** - Subdomain part of `{subdomain}.hyvorblogs.io`  which is given to the blogger.
- **Route** - Routes of the blog that determines how to render a page or what output to return for a specific URL path. See [routes](routes)
- **HB** - Hyvor Blogs
- **Rendering** - Combining a theme (template) with blog data and returning HTML output. See the below image.

<figure>
    <img src="/img/docs/theme-dev-rendering.png" alt="Themes Rendering in Hyvor Blogs" width="400" />
    <figcaption>Rendering in Hyvor Blogs</figcaption>
</figure>

## Basics

* Templating language is [Twig 3.0](https://twig.symfony.com/doc/)
* Styling supports [SCSS](https://sass-lang.com/), but you may just use CSS
* Config and language files are written in [YAML](https://yaml.org/).

## Routes {#routes}

To start theme development, it is essential to understand how Routes work in Hyvor Blogs. Routes are blog-level configurations, which means they are configured by the blogger. Hyvor Blogs comes with default routes that are usually enough for a simple blog.

Before continuing, we recommend you to read our [Routes](routes) guide to fully understand how routes work.

## Starting Development

Let's set up your local development environment.

* First, you need a DEV blog. Create one at [/console/new/dev](https://blogs.hyvor.com/console/new/dev). DEV blogs are similar to normal blogs in Hyvor Blogs, however they are free, and can only be used for theme development.
* You will get a subdomain in the `dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` format. We will need this later.

For the next steps, you need [Node.js](https://nodejs.org/en/) (and npm). As a front-end developer, we hope you already have it installed :)

* Next, install our CLI tool via npm

```bash
npm install -g hyvor-blogs-cli
```

* Create a new directory in your computer, which will contain all theme files and configurations.

```bash
mkdir my-theme
```

* `cd` to theme folder and run `hyvor-blogs-cli init`

```bash
cd my-theme
hyvor-blogs-cli init
```

This command will create the following folder structure inside your theme folder.

```plain
/
    /assets
    /lang
        en.yaml
    /styles
        index.scss
    /templates
        @base.twig
        author.twig
        index.twig
        post.twig
        tag.twig
    .env
    config.def.yaml
    config.yaml
```

You can also manually create this folder structure, if you wish.

* Next, open the `.env` file and update `SUBDOMAIN` with your DEV blog's subdomain (which you created earlier).

```plain
SUBDOMAIN=dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

* Then, run `hyvor-blogs-cli` command to serve your blog

```bash
hyvor-blogs-cli
```

* Open [127.0.0.1:8855](http://127.0.0.1:8855) in your browser to view the theme.

> **How it works**: The `hyvor-blogs-cli` command runs two processes under the hood: (1) a http server that works similar to a reverse proxy using our [delivery API](api-delivery), and (2) a process that watches your local file changes and syncs it with our production environment. So, whenever you add, edit, or delete a file within your theme folder, it will be synced with the theme files in your DEV blog.
> 
> **Security Notice 1**: Because all files in your theme directory are synced with our production system, never add any confidential files there.
> 
> **Security Notice 2**: Do not share your DEV subdomain publicly. It will allow other users to change theme files in your DEV blog. If you are using GIT for versioning, make sure to add `.env` to `.gitignore`.

## Folder Structure

As you see, there are four folders in a HB theme folder. Nested folders are **not supported**.

```plain
/
    /templates
    /styles
    /assets
    /lang
    config.yaml
```

* **templates**: All Twig template files go here. See [templates](themes-templates).
* **styles**: All SCSS files go here. See [styling](themes-styles).
* **assets**: You may add SVGs, PNGs, font files, or Javascript files here. All files in this directory are publicly accessible via the `/assets/{file_name}` route. Any file type is supported.
* **lang**: All language files go here. See [internationalization](themes-internationalization).

The root folder contains config files. See [configuration](themes-config).