<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import ThemeDevRenderingImg from './theme-dev-rendering.png';
	import { Callout, CodeBlock } from '@hyvor/design/components';
</script>

<h1 id="overview">Overview</h1>

<p>
	Hyvor Blogs themes are fully customizable. If you have some experience with HTML, CSS, and
	Javascript, you can easily build your own theme from scratch. This page is an overview to help you
	get started. All official themes are in the <a
		href="https://github.com/hyvor/hyvor-blogs-themes"
		rel="nofollow">hyvor-blogs-themes</a
	> repository. Feel free to review the source code of the other themes.
</p>

<p>Here's some of commonly used terms in this documentation:</p>
<ul>
	<li><b>Theme Developer</b> - the person who develops a theme (must be you!).</li>
	<li>
		<b>Blogger</b> - The person who owns the blog. They can install the theme you create and edit it
		through the console.
	</li>
	<li>
		<b>Subdomain</b> - Subdomain part of <code>{`{subdomain}`}</code>.hyvorblogs.io which is given
		to the blogger.
	</li>
	<li>
		<b>Route</b> - Routes of the blog that determines how to render a page or what output to return
		for a specific URL path. See <a href="/docs/routes">routes</a>
	</li>
	<li><b>HB</b> - Hyvor Blogs</li>
	<li>
		<b>Rendering</b> - Combining a theme (template) with blog data and returning HTML output. See the
		below image.
	</li>
</ul>

<DocsImage src={ThemeDevRenderingImg} alt="Theme Rendering" />

<h2 id="basics">Basics</h2>
<ul>
	<li>
		Templating language is <a href="https://twig.symfony.com/doc/" rel="nofollow">Twig 3.0</a>
	</li>
	<li>
		Styling supports <a href="https://sass-lang.com/" rel="nofollow">SCSS</a>, but you may just use
		CSS
	</li>
	<li>
		Config and language files are written in <a href="https://yaml.org/" rel="nofollow">YAML</a>.
	</li>
</ul>

<h2 id="routes">Routes</h2>
<p>
	To start theme development, it is essential to understand how Routes work in Hyvor Blogs. Routes
	are blog-level configurations, which means they are configured by the blogger. Hyvor Blogs comes
	with default routes that are usually enough for a simple blog.
</p>

<p>
	Before continuing, we recommend you to read our <a href="/docs/routes">Routes</a> guide to fully understand
	how routes work.
</p>

<h2 id="single-css-file">Single CSS File</h2>

<p>There is only a single CSS file in the blog, <code>styles.css</code>.</p>

<h2 id="flashload">Flashload</h2>
<p>
	<a href="https://github.com/hyvor/flashload">Flashload</a> is added to all blogs by default.
	Therefore, it is important to keep Flashload in mind while designing themes. Please take a minute
	and read the <a href="https://github.com/hyvor/flashload#readme">Flashload documentation</a> to get
	the idea of how it works.
</p>
<p>
	Why Flashload? Browser reloads are slow. They load the same CSS/JS resources multiple times making
	page rendering slower. Flashload starts loading other pages even before the user clicks the link.
	It makes navigation smoother. It simply turns the blog into a <b>Single Page Application (SPA)</b
	>!
</p>

<p>
	We previously learned that there's only one <code>styles.css</code> for a blog that contains all
	CSS of the blog. This <code>styles.css</code> should be loaded inside the <code>{`<head>`}</code>
	of the page. When the user navigates to another page, Flashload sends an AJAX request to that path
	and pre-fetches the HTML page. Then, it updates <b>only the</b> <code>{`<body>`}</code>
	<b>part</b>. (Remember, we already have all CSS loaded in the first request, so we don't want to
	load it again).
</p>

<p>The simple rule is to add shared resources of the blog to <code>{`<head>`}</code>.</p>

<h2 id="caching">Caching</h2>
<p>
	Another important behavior of HB is that we use caching EXTENSIVELY. We use a technique called <b
		>first-request-caching</b
	>.
</p>

<ul>
	<li>Someone requests <code>{`/hello-world`}</code> path of a blog.</li>
	<li>
		We don't have any cached output for this path. We fetch data from our database, combine it with
		the template, and generate the HTML output, and send the response back to the user. Behind the
		scenes, we save the generated HTML output in our cache.
	</li>
	<li>
		When someone else requests the same path, the HTML output is directly sent from the cache. It
		does not go through the rendering process.
	</li>
</ul>

<p>
	When using a cache, clearing cache is the most important thing. We have to make sure outdated
	content is not delivered when something changes. Here are the events that we clear cache for each
	scope.
</p>

<ul>
	<li>whenever whatever data is changed in the blog</li>
	<li>whenever the theme is edited</li>
	<li>on January 1st</li>
</ul>

<p>And,</p>

<ul>
	<li>
		<code>/search</code> and <code>{`/p/{hash}`}</code> (preview pages) routes are always dynamic, never
		cached.
	</li>
</ul>

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
<p>Let's set up your local development environment.</p>
<ul>
	<li>
		First, you need a DEV blog. Create one at <a href="/console/new/dev">/console/new/dev</a>. DEV
		blogs are similar to normal blogs in Hyvor Blogs, however they are free, and can only be used
		for theme development.
	</li>
	<li>
		You will get a subdomain in the <code>dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</code> format. We
		will need this later.
	</li>
</ul>

<p>
	For the next steps, you need <a href="https://nodejs.org/en/" rel="nofollow">Node.js</a> (and npm).
	As a front-end developer, we hope you already have it installed :)
</p>

<ul>
	<li>Next, install our CLI tool via npm</li>
</ul>

<CodeBlock
	code={`
                npm install -g hyvor-blogs-cli
                `}
/>

<ul>
	<li>
		Create a new directory in your computer, which will contain all theme files and configurations.
	</li>
</ul>

<CodeBlock
	code={`
                    mkdir my-theme
                    `}
/>

<ul>
	<li><code>cd</code> to theme folder and run <code>{`hyvor-blogs-cli init`}</code></li>
</ul>

<CodeBlock
	code={`
                        cd my-theme
                        hyvor-blogs-cli init
                        `}
/>

<p>This command will create the following folder structure inside your theme folder.</p>

<CodeBlock
	code={`
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
                    `}
/>

<p>You can also manually create this folder structure, if you wish.</p>

<ul>
	<li>
		Next, open the <code>.env</code> file and update <code>SUBDOMAIN</code> with your DEV blog's subdomain
		(which you created earlier).
	</li>
</ul>

<CodeBlock
	code={`
                    SUBDOMAIN=dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
                    `}
/>

<ul>
	<li>Then, run <code>hyvor-blogs-cli</code> command to serve your blog</li>
</ul>

<CodeBlock
	code={`
                        hyvor-blogs-cli
                        `}
/>
<ul>
	<li>Open your blog (dev-xxx.hyvorblogs.io) in your browser to view the theme.</li>
</ul>

<Callout type="info">
	<p>
		<b>How it works:</b> The <code>hyvor-blogs-cli</code> command runs a process that watches your local
		file changes and syncs it with our production environment. So, whenever you add, edit, or delete
		a file within your theme folder, it will be synced with the theme files in your DEV blog.
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
<p>
	As you see, there are four folders in a HB theme folder. Nested folders are <b>not supported</b>.
</p>

<CodeBlock
	code={`
            /
                /templates
                /styles
                /assets
                /lang
                config.yaml
            `}
/>

<ul>
	<li>
		<b>templates</b>: All Twig template files go here. See
		<a href="/docs/themes-templates">templates</a>.
	</li>
	<li><b>styles</b>: All SCSS files go here. See <a href="/docs/themes-styles">styling</a>.</li>
	<li>
		<b>assets</b>: You may add SVGs, PNGs, font files, or Javascript files here. All files in this
		directory are publicly accessible via the /assets <code>{`/{file_name}`}</code> route. Any file type
		is supported.
	</li>
	<li>
		<b>lang</b>: All the anguage files go here. See
		<a href="/docs/themes-internationalization">internationalization</a>.
	</li>
</ul>

<p>The root folder contains config files. See <a href="/docs/themes-config">configuration</a>.</p>
