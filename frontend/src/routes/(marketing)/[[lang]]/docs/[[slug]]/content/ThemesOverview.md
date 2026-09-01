<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout } from '@hyvor/design/components';
</script>

# Overview

Hyvor Blogs themes are fully customizable. If you have some experience with HTML, CSS, and
Javascript, you can easily build your own theme from scratch. This page is an overview to help you
get started. All official themes are in the <a href="https://github.com/hyvor/hyvor-blogs-themes" rel="nofollow">hyvor-blogs-themes</a> repository. Feel free to review the source code of the other themes.

Here's some of commonly used terms in this documentation:

- **Theme Developer** - the person who develops a theme (must be you!).
- **Blogger** - The person who owns the blog. They can install the theme you create and edit it through the console.
- **Subdomain** - Subdomain part of `{subdomain}`.hyvorblogs.io which is given to the blogger.
- **Route** - Routes of the blog that determines how to render a page or what output to return for a specific URL path. See [routes](/docs/routes)
- **HB** - Hyvor Blogs
- **Rendering** - Combining a theme (template) with blog data and returning HTML output. See the below image.

<DocsImage src="/images/docs/overview/theme-dev-rendering.png" alt="Theme Rendering" />

<h2 id="basics">Basics</h2>

- Templating language is <a href="https://twig.symfony.com/doc/" rel="nofollow">Twig 3.0</a>
- Styling supports <a href="https://sass-lang.com/" rel="nofollow">SCSS</a>, but you may just use CSS
- Config and language files are written in <a href="https://yaml.org/" rel="nofollow">YAML</a>.

<h2 id="routes">Routes</h2>

To start theme development, it is essential to understand how Routes work in Hyvor Blogs. Routes
are blog-level configurations, which means they are configured by the blogger. Hyvor Blogs comes
with default routes that are usually enough for a simple blog.

Before continuing, we recommend you to read our [Routes](/docs/routes) guide to fully understand
how routes work.

<h2 id="single-css-file">Single CSS File</h2>

There is only a single CSS file in the blog, `styles.css`.

<h2 id="flashload">Flashload</h2>

[Flashload](https://github.com/hyvor/flashload) is added to all blogs by default.
Therefore, it is important to keep Flashload in mind while designing themes. Please take a minute
and read the
[Flashload documentation](https://github.com/hyvor/flashload#readme) to get the idea of how
it works.

Why Flashload? Browser reloads are slow. They load the same CSS/JS resources multiple times making
page rendering slower. Flashload starts loading other pages even before the user clicks the link.
It makes navigation smoother. It simply turns the blog into a **Single Page Application (SPA)**!

We previously learned that there's only one `styles.css` for a blog that contains all
CSS of the blog. This `styles.css` should be loaded inside the
`<head>`
of the page. When the user navigates to another page, Flashload sends an AJAX request to that path and
pre-fetches the HTML page. Then, it updates **only the** `<body>`
**part**. (Remember, we already have all CSS loaded in the first request, so we don't want to
load it again).

The simple rule is to add shared resources of the blog to `<head>`.

<h2 id="caching">Caching</h2>

Another important behavior of HB is that we use caching EXTENSIVELY. We use a technique called **first-request-caching**.

- Someone requests `/hello-world` path of a blog.
- We don't have any cached output for this path. We fetch data from our database, combine it with the template, and generate the HTML output, and send the response back to the user. Behind the scenes, we save the generated HTML output in our cache.
- When someone else requests the same path, the HTML output is directly sent from the cache. It does not go through the rendering process.

When using a cache, clearing cache is the most important thing. We have to make sure outdated
content is not delivered when something changes. Here are the events that we clear cache for each
scope.

- whenever whatever data is changed in the blog
- whenever the theme is edited
- on January 1st

And,

- `/search` and `/p/{hash}` (preview pages) routes are always dynamic, never cached.

<Callout type="info">
	<p>
		⚠️ Caching makes the blog super fast. However, it puts some limitations to theme development.
		You can't render dynamic data like "current date" using Twig. Due to cache, users may see an old
		date. If absolutely required, you have to use Javascript to render dynamic content inside user's
		browser. However, displaying the "publish date" of a post works fine because we clear cache
		whenever the post is updated. Also, displaying the current year will work, because we will make
		sure to clear the cache on the 1st of January.
	</p>
</Callout>

<h2 id="starting-developement">Starting Development</h2>

Let's set up your local development environment.

- First, you need a DEV blog. Create one at [/console/new/dev](/console/new/dev). DEV blogs are similar to normal blogs in Hyvor Blogs, however they are free, and can only be used for theme development.
- You will get a subdomain in the `dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` format. We will need this later.

For the next steps, you need <a href="https://nodejs.org/en/" rel="nofollow">Node.js</a> (and npm).
As a front-end developer, we hope you already have it installed :)

- Next, install our CLI tool via npm

```bash
npm install -g hyvor-blogs-cli
```

- Create a new directory in your computer, which will contain all theme files and configurations.

```bash
mkdir my-theme
```

- `cd` to theme folder and run `hyvor-blogs-cli init`

```bash
cd my-theme
hyvor-blogs-cli init
```

This command will create the following folder structure inside your theme folder.

```
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

- Next, open the `.env` file and update `SUBDOMAIN` with your DEV blog's subdomain (which you created earlier).

```bash
SUBDOMAIN=dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

- Then, run `hyvor-blogs-cli` command to serve your blog

```bash
hyvor-blogs-cli
```

- Open your blog (dev-xxx.hyvorblogs.io) in your browser to view the theme.

<Callout type="info">
	<p>
		<b>How it works:</b> The <code>hyvor-blogs-cli</code> command runs a process that watches your local
		file changes and syncs it with our production environment. So, whenever you add, edit, or delete a
		file within your theme folder, it will be synced with the theme files in your DEV blog.
	</p>

    <p>
    	<b>Security Notice 1:</b> Because all files in your theme directory are synced with our production
    	system, never add any confidential files there.
    </p>

    <p>
    	<b>Security Notice 2:</b> Do not share your DEV subdomain publicly. It will allow other users to
    	change theme files in your DEV blog. If you are using GIT for versioning, make sure to add
    	<code>.env</code>
    	to <code>.gitignore</code>.
    </p>

</Callout>

<h2 id="folder-structure">Folder Structure</h2>

As you see, there are four folders in a HB theme folder. Nested folders are **not supported**.

```
/
    /templates
    /styles
    /assets
    /lang
    config.yaml
```

- **templates**: All Twig template files go here. See [templates](/docs/themes-templates).
- **styles**: All SCSS files go here. See [styling](/docs/themes-styles).
- **assets**: You may add SVGs, PNGs, font files, or Javascript files here. All files in this directory are publicly accessible via the /assets `/{file_name}` route. Any file type is supported.
- **lang**: All the anguage files go here. See [internationalization](/docs/themes-internationalization).

The root folder contains config files. See [configuration](/docs/themes-config).
