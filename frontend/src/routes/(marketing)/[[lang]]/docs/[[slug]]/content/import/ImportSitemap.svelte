<script>
	import { CodeBlock, Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

<h1>Import from Sitemap</h1>

<p>
	This method allows you to import blog posts from <strong>ANY PLATFORM</strong> assuming:
</p>

<ul>
	<li>
		All your posts have a similar HTML structure, and data can be extracted from the HTML using meta
		tags and, optionally, CSS selectors.
	</li>
	<li>
		Has a sitemap (XML or TXT) with all the URLs of your posts. If you don't have a sitemap, you can
		easily use an online tool to generate one. Or, if you have a list of URLs in a TXT file, you can
		use that as well.
	</li>
</ul>

<h2 id="how">How it works</h2>

<ul>
	<li>Submit your sitemap (<code>.xml</code> or <code>.txt</code>)</li>
	<li>Provide CSS selectors to help us extract data from your HTML pages</li>
	<li>Test your CSS against a couple of your pages to make sure data is correctly parsed</li>
	<li>Finally, import from the sitemap</li>
</ul>

<h2 id="data">Data that can be extracted</h2>

<p>Our importer is capable of extracting the following data from your HTML pages:</p>

<Table columns="1fr 2fr">
	<TableRow head>
		<div>Data</div>
		<div>Extraction Method</div>
	</TableRow>
	<TableRow>
		<div>Post title</div>
		<div>CSS selector or <code>{`<title>`}</code></div>
	</TableRow>
	<TableRow>
		<div>Post description</div>
		<div>CSS selector or <code>{`<meta name="description">`}</code></div>
	</TableRow>
	<TableRow>
		<div>Post content</div>
		<div>CSS selector. See <a href="#content">content importing</a></div>
	</TableRow>
	<TableRow>
		<div>Post published date</div>
		<div>CSS selector or <code>{`<meta property="article:published_time">`}</code></div>
	</TableRow>
	<TableRow>
		<div>Post featured image</div>
		<div><code>{`<meta property="og:image">`}</code></div>
	</TableRow>
	<TableRow>
		<div>Post slug</div>
		<div>from URL</div>
	</TableRow>
</Table>

<p>
	It <strong>does not</strong> support importing the following data:
</p>

<ul>
	<li>Tags - no tags will be added to posts</li>
	<li>Authors - the owner of the blog will be added as the author of all posts</li>
</ul>

<h2 id="css-selectors">CSS Selectors</h2>

<p>
	Each blog has a different structure. Therefore, we need to know how to extract data from your HTML
	pages. You can provide CSS selectors for each data type.
</p>

<p>For example, in this blog:</p>

<DocsImage src="/images/docs/import/css-selectors.png" alt="CSS Selectors" />

<p>You can set the following CSS selectors:</p>

<ul>
	<li>
		<strong>Post title</strong>: <code>h1</code>
	</li>
	<li>
		<strong>Post content</strong>: <code>section.post-content</code>
	</li>
	<li>
		<strong>Post published date</strong>: <code>time.publish-date</code>
	</li>
</ul>

<p>
	Note that only the post content selector is required. Other selectors are optional. If you don't
	provide a selector for a data type, we will try to extract it from the HTML page using meta tags
	as explained in the above table.
</p>

<h2 id="content">Content</h2>

<p>
	The importer will automatically detect most styles (bold, italic) and blocks (paragraphs,
	blockquotes) from generic HTML tags.
</p>

<p>It currently does not support importing the following block types:</p>

<ul>
	<li>Link Bookmark</li>
	<li>Custom HTML/Twig</li>
</ul>

<p>It has limited support for the following block types:</p>

<ul>
	<li>
		Embed - We will try to import iframes as embeds (ex: Youtube embed). However, we cannot
		guarantee that it will work for all embeds.
	</li>
</ul>

<h3 id="content-exclude">Exclude content</h3>

<p>
	You can exclude some parts of your content using CSS selectors. For example, if you have ads in
	your blog posts, you can exclude them by adding a CSS selector to <strong
		>Post Content Exclude</strong
	>:
</p>

<CodeBlock language="css" code=".post-content > .ad" />

<p>To exclude multiple elements, separate them using a comma:</p>

<CodeBlock language="css" code=".ad, .newsletter-signup" />

<h2 id="import-images">Importing images</h2>

<p>
	If you are completely migrating to Hyvor Blogs, it is possible that images will no longer will be
	available in the original server. Therefore, we recommend you to import images to Hyvor Blogs. To
	do this, make sure to keep the Import Images on. Then, we will import featured images and all
	images in the post content into your blog <a href="/docs/media">media library</a>. The image
	should be less than 50MB to be imported.
</p>

<h2 id="test-import">Test & Import!</h2>

<p>
	Once you have provided all the required information, you can test your CSS selectors against a
	couple of your posts. If everything is fine, you can import all posts from the sitemap.
</p>

<p>If you have any issues, feel free to contact us</p>
