<script lang="ts">
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

<h1>Routes</h1>
<p>
	Routes are used to configure how specific requests are handled. A route matches the request path
	and determines what output to send back to the user. For example, if the request path is <code
		>/tag/my-tag</code
	>, route determines that it should render <code>tag.twig</code> and send response back to the user.
</p>

<ul>
	<li><a href="/docs/routes#defaults">Default Routes</a></li>
	<li><a href="/docs/routes#permalinks">Customizing Post/Page Permalinks</a></li>
	<li><a href="/docs/routes#customizing-other">Customizing Other Default Routes</a></li>
	<li><a href="/docs/routes#custom">Custom Routes</a></li>
	<li><a href="/docs/routes#conflicts">Route Conflicts</a></li>
	<li><a href="/docs/routes#suffix-prefix">Suffixes & Prefixes</a></li>
	<li><a href="/docs/routes#website">Making a Website</a></li>
	<li><a href="/docs/routes#collections">Multiple Post Collections</a></li>
</ul>

<p>Route Settings: <b>Console → Settings → Routes</b></p>

<h2 id="defaults">Default Routes</h2>
<p>A new blog will have these 5 default routes.</p>
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

<p>In addition to those default routes, there are some special, non-customizable routes.</p>

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

<p>
	You can change the match value of <code>post</code> and <code>page</code> routes to change
	post/page permalinks. By default, it looks like <code>{`/{slug}`}</code>. You may change it to a
	different structure which may have date, tag, and/or author name. Here are some examples.
</p>

<ul>
	<li><code>{`/{year}/{month}/{day}/{slug}`}</code></li>
	<li><code>{`/{tag}/{slug}`}</code></li>
	<li><code>{`/{author}/{slug}`}</code></li>
</ul>
<p>
	Note that <code>{`{slug}`}</code> is always required. The following placeholders are supported.
</p>

<p>Basic placeholders:</p>

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

<p>Time-based placeholders:</p>
<ul>
	<li>Represent the post's <b>publish time</b></li>
	<li>Only English lowercase is supported for month and day names</li>
</ul>

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
<p>
	Similar to <code>post</code> and <code>page</code>, <code>routes</code>, other default routes (<code
		>index</code
	>, <code>tag</code>, <code>author</code>, and <code>search</code>) routes are customizable to
	some extent. Here are some ideas:
</p>
<ul>
	<li><code>{`/author/{slug}`}</code>→<code>{`/creator/{slug}`}</code></li>
	<li><code>{`/tag/{slug}`}</code>→<code>{`/category/{slug}`}</code></li>
	<li><code>{`/`}</code>→<code>{`/blog`}</code></li>
</ul>

<Callout type="info">
	<p>
		Please note that you (or theme developers) can also add custom routes by adding <code
			>{`route-{route}`}</code
		>.twig files to template files. See <a href="/docs/themes-templates#custom-routes">here</a> from
		more details.
	</p>
</Callout>

<h2 id="custom">Custom Routes</h2>
<p>
	In addition to the default 6 routes, you can add your own routes. Some examples use cases are:
</p>
<ul>
	<li>Creating custom landing pages</li>
	<li>Creating <a href="/docs/routes#collections">new post collections</a></li>
	<li>Creating custom RSS feeds, for example, for a podcast</li>
</ul>

<Callout type="info">
	<p>
		Please note that you (or theme developers) can also add custom routes by adding <code
			>{`route-{route}`}</code
		>.twig files to template files. See <a href="/docs/themes-templates#custom-routes">here</a> from
		more details.
	</p>
</Callout>

<h2 id="conflicts">Route Conflicts</h2>
<p>
	Usually, route conflicts can happen when two or more routes has the same match value. In Hyvor
	Blogs, <code>post</code> and <code>page</code> routes can have the same match values. (You can
	see, the default values of those two routes are the same: <code>{`/{slug}`}</code>). However,
	other routes match cannot have duplicate match values.
</p>

<h2 id="suffix-prefix">Suffixes and Prefixes</h2>
<p>These suffixes are supported:</p>
<ul>
	<li><code>/feed</code> - For the atom feed (<code>posts_filter</code>should be set)</li>
	<li><code>{`/page/{page_number}`}</code> - For pagination</li>
</ul>

<p>
	Matches can be prefixed with a language code. See languages for setting up multiple <a
		href="/docs/languages">languages</a
	> on your blog.
</p>

<h2 id="website">Making a Website</h2>
<p>
	We usually call a website "a blog" when it has posts and the home page lists all of them. That
	is the default behavior of Hyvor Blogs. Even out of its purpose, you can use Hyvor Blogs to
	create a general website. For example, you can create a landing page for the homepage, and have
	your blog in the <code>/blog</code> subdirectory.
</p>

<h2 id="collections">Multiple Post Collections</h2>
<p>
	By default, your blog has one post collection, and all posts will be listed the index page. What
	if you want to have to separate collections, for example, blog posts and podcast episodes in the
	blog? You can use routes and <a href="/docs/tags">tags</a> to achieve this.
</p>

<ul>
	<li>We add a <code>podcast</code> tag to the all podcast posts.</li>
	<li>
		We can customize the <code>index</code> (<code>/</code>) route and its template (<code
			>index.twig</code
		>) to show an overview of blog posts and podcast episodes. To do this, you will need to
		update <code>index.twig</code> and use our <a href="/docs/api-data">Data API</a> to fetch posts
		separately.
	</li>
	<li>We add two new routes with a new template:</li>
	<ul>
		<li>
			<code>/blog</code> -> to list blog posts (using the filter
			<code>{`tag.slug != podcast`}</code>)
		</li>
		<li>
			<code>/podcast</code> -> to list podcast episodes (using the filter
			<code>{`tag.slug = podcast`}</code>)
		</li>
	</ul>
</ul>
