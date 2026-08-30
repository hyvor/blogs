<script lang="ts">
	import { Table, TableRow, Callout } from '@hyvor/design/components';
</script>

# Templates

When a blog gets a request, first, we match its path to a Route (let's assume the route `post` for `/hello-world`). Then, we fetch required data from our database, then we call the Twig template file defined in that route (`post.twig`).

Inside this file, you can include other files or even use <a href="https://twig.symfony.com/doc/3.x/templates.html#template-inheritance" rel="nofollow">inheritance</a>. You can even call our [Data API](/docs/api-data) to fetch more data (More on that below)!

<h2 id="twig">Twig</h2>

We use <a href="https://twig.symfony.com/doc/3.x/" rel="nofollow">Twig 3.0</a> for templating. It is a powerful language with plenty of in-built tags, filters, and functions. Twig also has nice, easy-to-follow documentation, which was one reason we chose Twig over other template languages. If you haven't used it ever, go through the <a href="https://twig.symfony.com/doc/3.x/templates.html" rel="nofollow">Twig for Template Designers</a> page, and you will get an idea of how it works. Basically, it's HTML with superpowers.

### Templates

This folder contains templates files. There are several types of template files

<Table columns="1fr 3fr 2fr" hover>
	<TableRow head>
		<div>Type</div>
		<div>Description</div>
		<div>Example</div>
	</TableRow>

    <TableRow>
    	<div>Main</div>
    	<div>These template files are rendered directly.</div>
    	<div><code>index.twig</code> <code>post.twig</code></div>
    </TableRow>

    <TableRow>
    	<div>Partial</div>
    	<div>
    		These templates are not rendered directly but included in main template files. They start with
    		an underscore (<code>_</code>)
    	</div>
    	<div><code>_footer.twig</code></div>
    </TableRow>

    <TableRow>
    	<div>Route</div>
    	<div>
    		These templates are used to define custom routes for a blog. The file name starts with <code
    			>route-</code
    		>. See <a href="/docs/themes-templates#custom-routes">custom routes</a> below
    	</div>
    	<div><code>route-authors.twig</code></div>
    </TableRow>

    <TableRow>
    	<div>Component</div>
    	<div>
    		These templates are used to define new HTML structures for complex components like link
    		previews. See <a href="/docs/writing#link-bookmark">Link Bookmark</a>.
    	</div>
    	<div><code>component-rich-link.twig</code></div>
    </TableRow>

</Table>

<h2 id="variables">Theme Variables</h2>

- The theme developer (you) creates the **theme**
- The blogger creates the content **(data)**
- HB combines the **theme** and **data** and generates the blog

When rendering the twig templates, we send data into your template file as objects. You will use this data to generate a beautiful UI.

There are 4 main objects in HB: `Blog` , `Post` , `Tag` , and `Author`. These objects are explained in the [Data API](/docs/api-data) page.

<Table columns="1fr 1fr 3fr" hover>
	<TableRow head>
		<div>Variable Name</div>
		<div>Available Routes</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>_blog</code></div>
    	<div>(all)</div>
    	<div>A Blog object, that includes all blog-level data/settings.</div>
    </TableRow>

    <TableRow>
    	<div><code>_lang</code></div>
    	<div>(all)</div>
    	<div>
    		A Language Object for the <b>current</b> language. Should also be placed in
    		<code>{`<html lang="{{ _lang.code }}">`}</code>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>_config</code></div>
    	<div>(all)</div>
    	<div>Theme config (<code>config.yaml</code>) as an object</div>
    </TableRow>

    <TableRow>
    	<div><code>_route</code></div>
    	<div>(all)</div>
    	<div>Current <a href="/docs/routes">route</a> name</div>
    </TableRow>

    <TableRow>
    	<div><code>_posts</code></div>
    	<div>(all)</div>
    	<div>
    		An array of Post objects, filtered by the <a href="/docs/routes">route</a>'s filter value
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>_featured_post</code></div>
    	<div>index</div>
    	<div>An array of Posts objects (all featured posts)</div>
    </TableRow>

    <TableRow>
    	<div><code>_post</code></div>
    	<div>post and page</div>
    	<div>A Post object</div>
    </TableRow>

    <TableRow>
    	<div><code>_tag</code></div>
    	<div>tag</div>
    	<div>A Tag object (the current tag)</div>
    </TableRow>

    <TableRow>
    	<div><code>_author</code></div>
    	<div>author</div>
    	<div>An Author object (the current author)</div>
    </TableRow>

    <TableRow>
    	<div><code>_branding</code></div>
    	<div>all</div>
    	<div>Boolean, whether to show Hyvor Blogs branding.</div>
    </TableRow>

</Table>

Each Route gets different variables. We prefix each variable with `_` so that it won't conflict with the variables you define inside the theme files (Obviously, you shouldn't prefix `_` your variables inside the Twig template)

<h2 id="placeholders">Placeholders</h2>

You are required to put some placeholders in your theme to make a few things work.

<Table columns="1fr 1fr 4fr" hover>
	<TableRow head>
		<div>Placeholder</div>
		<div>Scopes</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>_head</code></div>
    	<div>(all)</div>
    	<div>
    		place before <code>{`</head>`}</code>. We automatically add SEO tags, styles.css link, and
    		code_head set by the blogger
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>_foot</code></div>
    	<div>(all)</div>
    	<div>place before <code>{`</body>`}</code>. We place the code_foot set by the blogger</div>
    </TableRow>

    <TableRow>
    	<div><code>_comments</code></div>
    	<div>post and page</div>
    	<div>to embed the commenting system</div>
    </TableRow>

    <TableRow>
    	<div><code>_comment_count</code> (optional)</div>
    	<div>post and page</div>
    	<div>
    		to render the comment count of that page. For example, some themes have comment count at the
    		top with a link to the comments section to encourage more comments. Only works when Hyvor Talk
    		is connected
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>_newsletter</code></div>
    	<div>post and page</div>
    	<div>to embed the newsletter subscription form</div>
    </TableRow>

</Table>

Sending all placeholders (except ` _lang`) through the `template` filter is absolutely required to make them render as templates.

```html
{{ _head | template }}
```

<Callout type="info">
	<p>
		<code>{`{{ _head | template }}`}</code> is equal to
		<code>{`{{ include(template_from_string(_head)) }}`}</code>
		in Twig. We defined the custom <code>template</code> filter to make it easier for you to write it,
		as it is used frequently in HB templates.
	</p>
</Callout>

<h2 id="twig-helpers">Twig Helpers</h2>

We provide a few custom Twig functions and filters to make writing templates easier.

<h3 id="helper-functions">Functions</h3>

- `data` - a function to call the Data API. See Fetching data below.

```html
{% set posts = data(endpoint="posts", filter="author.slug=user") %}
```

- `icon` - a function to get an icon.

```html
{{ icon('bootstrap', 'arrow-down', 20, 20) }}
```

Function definition: `icon(iconLibrary, iconName, width, height)`

- All icon names are lowercase, and words are separated by `-` (`arrow-down`).
- These icon libraries are supported

<ul>
	<li><a href="https://icons.getbootstrap.com/" rel="nofollow">bootstrap</a></li>
	<li>
		<a href="https://fontawesome.com/icons" rel="nofollow">fontawesome</a>Free icons only
	</li>

    <ul>
    	<li>append <code>-regular</code> to regular icons (<code>calendar-regular</code>)</li>
    	<li>append <code>-solid</code> to solid icons (<code>calendar-solid</code>)</li>
    	<li>Do not append anything for brand icons (<code>github</code>)</li>
    </ul>

    <li><a href="https://ionic.io/ionicons" rel="nofollow">ionicons</a></li>
    <li><a href="https://heroicons.com/" rel="nofollow">heroicons</a></li>
    <ul>
    	<li>append <code>-solid</code> to solid icons (<code>archive-solid</code>)</li>
    	<li>append <code>-outline</code> to outline icons (<code>archive-outline</code>)</li>
    </ul>

    <li><a href="https://primer.github.io/octicons" rel="nofollow">octicons</a></li>
    <li><a href="https://css.gg/" rel="nofollow">css.gg</a></li>

    <Callout type="info">
    	<p>
    		Under the hood, we use the <a href="https://github.com/hyvor/php-svg-icons">php-svg-icons</a
    		> open-source library. If you need to add more icon libraries, please send a PR there.
    	</p>
    </Callout>

</ul>

<h3 id="helper-filters">Filters</h3>

<ul>
	<li><code>asset_url</code> - a filter to link assets</li>

    <ul>
    	<li>Turns an asset filename into its absolute URL.</li>
    	<li>Adds last updated timestamp as a query param (to bypass browser cache on updates)</li>

```html
{{ 'script.js' | asset_url }}
```

```html
<script src="{{ 'script.js' | asset_url }}"></script>

// changes to:

<script src="https://subdomain.hyvorblogs.io/assets/script.js?v=12931923993"></script>
```

    </ul>

    <li><code>asset</code> - a filter to directly print assets (only for text assets like SVGs)</li>

```html
{{ 'beauty.svg' | asset }}
```

    <li><code>pagination_page_url</code> - a filter to convert a page number to full URL</li>

```html
<a href="{{ _pagination.page_prev | pagination_page_url }}">Previous Page</a>
```

    <li>
    	<code>lang</code> - a filter for translations. Learn more in
    	<a href="/docs/themes-internationalization">internationalization</a>.
    </li>
    <li>
    	<code>lang_by_number</code> - See
    	<a href="/docs/themes-internationalization#lang-by-number"
    		>conditional strings based on a number</a
    	>.
    </li>
    <li>
    	<code>language_variant_url</code> - See
    	<a href="/docs/themes-internationalization#language-switcher">language switcher</a>
    </li>

    <li>
    	<code>toc</code> - a filter to generate a table of contents from a HTML string.

```html
{{ _post.content | toc }}
```

    	By default, all headings are included in the table of contents. You can set which levels to
    	include as follows:

```html
{{ _post.content | toc('2,3') }}
```

    </li>

</ul>

<Callout type="info">
	<p>
		The difference between functions and filters can be quite confusing in Twig. Our general rule is
		to use functions to compute things (<code>data</code> and <code>icon</code>) and use filters
		when apply a transformation (<code>asset_url</code>, <code>asset</code>, etc.).
	</p>
</Callout>

<h2 id="fetch-data">Fetching Data</h2>

Use the `data` function to fetch data from our [Data API](/docs/api-data).

```html
<!-- Fetch data -->
{% set recent_posts = data(endpoint="posts", sort="published_at DESC", limit="5") %}

<!-- Render UI -->
<div id="recent-posts">
	{% for post in recent_posts.data %} {% include '_recent-post-card.twig' with post %} {% endfor %}
</div>
```

Use the `endpoint` named argument to set the API endpoint. You can set all other parameters by just sending them as named arguments to the `data` Twig function (Ex: `sort="published_at DESC"`).

<h2 id="custom-routes">Custom Routes</h2>

There are two ways to add custom routes:

- The blogger can add custom routes from the console ([See docs](/docs/routes#custom)).
- Theme developers can define custom routes by adding files named `route-{route}.twig` to the `templates` folder.

The first option is more robust, and it providers easier way to automatically set input variables `_posts` (by filtering), `_tag`, `_author`, etc so you can access them without calling the Data API. But, as a theme developer, you will need to use the second option.

For example, let's say you decide that your theme want a page to list all authors of the blog. You can add a `route-authors.twig` to the `templates` folder. If the blog gets a request to `/authors`, this template will be rendered automatically.
