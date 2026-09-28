<script lang="ts">
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Routes

Routes are used to configure how specific requests are handled. A route matches the request path and determines what output to send back to the user. For example, if the request path is `/tag/my-tag`, route determines that it should render `tag.twig` and send response back to the user.

- [Default Routes](/docs/routes#defaults)
- [Customizing Post/Page Permalinks](/docs/routes#permalinks)
- [Customizing Other Default Routes](/docs/routes#customizing-other)
- [Custom Routes](/docs/routes#custom)
- [Route Conflicts](/docs/routes#conflicts)
- [Suffixes & Prefixes](/docs/routes#suffix-prefix)
- [Making a Website](/docs/routes#website)
- [Multiple Post Collections](/docs/routes#collections)

Route Settings: **Console → Settings → Routes**

<h2 id="defaults">Default Routes</h2>

A new blog will have these 5 default routes.

<Table columns="2fr 2fr 3fr 2fr 2fr" hover>
	<TableRow head>
		<div>Route Name</div>
		<div>Match</div>
		<div>Description</div>
		<div>Template</div>
		<div>Post Filter</div>
	</TableRow>

    <TableRow>
    	<div><code>post</code></div>
    	<div><code>{`/{slug}`}</code></div>
    	<div>Matches a post</div>
    	<div>post</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div><code>page</code></div>
    	<div><code>{`/{slug}`}</code></div>
    	<div>Matches a page</div>
    	<div>page,post</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div><code>index</code></div>
    	<div><code>{`/`}</code></div>
    	<div>Main index page (lists all posts)</div>
    	<div>index</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div><code>tag</code></div>
    	<div><code>{`/tag/{slug}`}</code></div>
    	<div>Tag index page (lists all posts of a specific tag)</div>
    	<div>tag,index</div>
    	<div><code>{`tag.slug = {slug}`}</code></div>
    </TableRow>

    <TableRow>
    	<div><code>author</code></div>
    	<div><code>{`/author/{slug}`}</code></div>
    	<div>Author index page (lists all posts of a specific author)</div>
    	<div>author,index</div>
    	<div><code>{`author.slug = {slug}`}</code></div>
    </TableRow>

</Table>

<Callout type="info">
	<p>
		Posts Filter is a <a href="https://github.com/hyvor/laravel-filterq">FilterQ expression</a>
		to filter posts. These filtered posts will be sent to the template as the
		<code>_posts</code>
		variable. It is only used in listing pages like index, tag, author. Matched params (<code
			>{`{slug}`}</code
		>) can be used in this expression.
	</p>
</Callout>

In addition to those default routes, there are some special, non-customizable routes.

<Table columns="2fr 2fr" hover>
	<TableRow head>
		<div>Match</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>{`/styles.css`}</code></div>
    	<div>The main CSS file of the blog (auto-generated from SCSS files in theme styles)</div>
    </TableRow>

    <TableRow>
    	<div><code>{`/assets/{file_name}`}</code></div>
    	<div>To serve files in the theme assets</div>
    </TableRow>

    <TableRow>
    	<div><code>{`/media/{file_name}`}</code></div>
    	<div>To serve uploaded media</div>
    </TableRow>

    <TableRow>
    	<div><code>{`/p/{hash}`}</code></div>
    	<div>To preview posts and pages</div>
    </TableRow>

    <TableRow>
    	<div><code>{`/robots.txt`}</code></div>
    	<div><a href="/docs/seo#robots">Robots.txt</a></div>
    </TableRow>

    <TableRow>
    	<div>
    		<code>{`/sitemap.xml`}</code>, <code>{`/sitemap-pages.xml`}</code>,
    		<code>{`/sitemap-posts-x.xml`}</code>
    	</div>
    	<div><a href="/docs/seo#sitemap">Sitemap</a></div>
    </TableRow>

</Table>

<h2 id="permalinks">Customizing Post/Page Permalinks</h2>

You can change the match value of `post` and `page` routes to change post/page permalinks. By default, it looks like `/{slug}`. You may change it to a different structure which may have date, tag, and/or author name. Here are some examples.

- `/{year}/{month}/{day}/{slug}`
- `/{tag}/{slug}`
- `/{author}/{slug}`

Note that `{slug}` is always required. The following placeholders are supported.

Basic placeholders:

<Table columns="2fr 2fr" hover>
	<TableRow head>
		<div>Placeholder</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>{`{slug}`}</code></div>
    	<div>Post slug (required always)</div>
    </TableRow>

    <TableRow>
    	<div><code>{`{tag}`}</code></div>
    	<div>Slug of the first tag of the post</div>
    </TableRow>

    <TableRow>
    	<div><code>{`{author}`}</code></div>
    	<div>Slug of the author of the post</div>
    </TableRow>

</Table>

<Callout type="info">
	<p>
		Please note that if you use <code>{`{tag}`}</code> (or <code>{`{author}`}</code>) in the
		post/page URL, all post <b>should have</b> at least one tag (or author). Otherwise, the post/page
		URL will show 404 error.
	</p>
</Callout>

Time-based placeholders:

- Represent the post's **publish time**
- Only English lowercase is supported for month and day names

<Table columns="2fr 2fr 2fr" hover>
	<TableRow head>
		<div>Placeholder</div>
		<div>Description</div>
		<div>Example</div>
	</TableRow>

    <TableRow>
    	<div><code>{`{year}`}</code></div>
    	<div>4-digit year</div>
    	<div><code>2022</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{year_short}`}</code></div>
    	<div>2-digit year</div>
    	<div><code>99</code> or <code>22</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{month}`}</code></div>
    	<div>2-digit month number</div>
    	<div><code>01</code> or <code>12</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{month_number}`}</code></div>
    	<div>month number without leading zero</div>
    	<div><code>1</code> to <code>12</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{month_short}`}</code></div>
    	<div>short month name</div>
    	<div><code>jan</code> to <code>dec</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{month_long}`}</code></div>
    	<div>long month name</div>
    	<div><code>january</code> to <code>december</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day}`}</code></div>
    	<div>2-digit day</div>
    	<div><code>01</code> to <code>31</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_number}`}</code></div>
    	<div>day number without leading zero</div>
    	<div><code>1</code> to <code>31</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_year}`}</code></div>
    	<div>ordinal day of the year</div>
    	<div><code>1</code> to <code>365</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_week}`}</code></div>
    	<div>3-letter weekday</div>
    	<div><code>mon</code> to <code>sun</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_week_long}`}</code></div>
    	<div>weekday</div>
    	<div><code>monday</code> to <code>sunday</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_week_number}`}</code></div>
    	<div>weekday as a number</div>
    	<div>from<code>1</code> to <code>7</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{hour}`}</code></div>
    	<div>hour of the day, in 24-format</div>
    	<div><code>00</code> to <code>23</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{minute}`}</code></div>
    	<div>minute of the hour</div>
    	<div><code>00</code> to <code>59</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{second}`}</code></div>
    	<div>second of the minute</div>
    	<div><code>00</code> to <code>59</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{unix}`}</code></div>
    	<div>UNIX timestamp in seconds</div>
    	<div><code>1448406000</code></div>
    </TableRow>

</Table>

<h2 id="customizing-other">Customizing Other Default Routes</h2>

Similar to `post` and `page`, `routes`, other default routes (`index`, `tag`, `author`, and `search`) routes are customizable to some extent. Here are some ideas:

- `/author/{slug}`→`/creator/{slug}`
- `/tag/{slug}`→`/category/{slug}`
- `/`→`/blog`

<Callout type="info">
	<p>
		Please note that you (or theme developers) can also add custom routes by adding <code
			>{`route-{route}`}</code
		>.twig files to template files. See <a href="/docs/themes-templates#custom-routes">here</a> from more
		details.
	</p>
</Callout>

<h2 id="custom">Custom Routes</h2>

In addition to the default 6 routes, you can add your own routes. Some examples use cases are:

- Creating custom landing pages
- Creating [new post collections](/docs/routes#collections)
- Creating custom RSS feeds, for example, for a podcast

<Callout type="info">
	<p>
		Please note that you (or theme developers) can also add custom routes by adding <code
			>{`route-{route}`}</code
		>.twig files to template files. See <a href="/docs/themes-templates#custom-routes">here</a> from more
		details.
	</p>
</Callout>

<h2 id="conflicts">Route Conflicts</h2>

Usually, route conflicts can happen when two or more routes has the same match value. In Hyvor Blogs, `post` and `page` routes can have the same match values. (You can see, the default values of those two routes are the same: `/{slug}`). However, other routes match cannot have duplicate match values.

<h2 id="suffix-prefix">Suffixes and Prefixes</h2>

These suffixes are supported:

- `/feed` - For the atom feed (`posts_filter`should be set)
- `/page/{page_number}` - For pagination

Matches can be prefixed with a language code. See languages for setting up multiple [languages](/docs/languages) on your blog.

<h2 id="website">Making a Website</h2>

We usually call a website "a blog" when it has posts and the home page lists all of them. That is the default behavior of Hyvor Blogs. Even out of its purpose, you can use Hyvor Blogs to create a general website. For example, you can create a landing page for the homepage, and have your blog in the `/blog` subdirectory.

<h2 id="collections">Multiple Post Collections</h2>

By default, your blog has one post collection, and all posts will be listed the index page. What if you want to have to separate collections, for example, blog posts and podcast episodes in the blog? You can use routes and [tags](/docs/tags) to achieve this.

- We add a `podcast` tag to the all podcast posts.
- We can customize the `index` (`/`) route and its template (`index.twig`) to show an overview of blog posts and podcast episodes. To do this, you will need to update `index.twig` and use our [Data API](/docs/api-data) to fetch posts separately.
- We add two new routes with a new template:
  - `/blog` -> to list blog posts (using the filter `tag.slug != podcast`)
  - `/podcast` -> to list podcast episodes (using the filter `tag.slug = podcast`)
