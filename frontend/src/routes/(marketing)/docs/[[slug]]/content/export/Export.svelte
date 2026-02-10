<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import exportGif from './export.gif';
	import { CodeBlock } from '@hyvor/design/components';
</script>

<h1>Export Data</h1>

<p>
	You own your data, and we make sure you can access them any time you need. You can currently
	export blog data in JSON format. We are working on adding WordPress export format and media
	exports soon!
</p>

<h2 id="how">How to export</h2>

<ul>
	<li>
		Go to <strong>Tools &rarr; Export</strong> in the Console
	</li>
	<li>
		Click on <strong>Export Now</strong> button
	</li>
</ul>

<p>
	It will take a couple of minutes depending on the size of your blog. You can track the progress
	in the <strong>History</strong> tab.
</p>

<DocsImage src={exportGif} alt="Exporting data" />

<h2 id="format">Export Format</h2>

<p>The exported JSON file has the following structure:</p>

<CodeBlock
	code={`
    {
        "blog": { a blog object },
        "languages": [
            language object,
            ...
        ],
        "posts": [
            post object,
            post object,
            post object,
            ...
        ],
        "users": [
            user object,
            ...
        ],
        "tags": [
            tag object,
            ...
        ],
        "media": [
            media object,
            ...
        ],
        "navigation": [
            navigation object
            ...
        ],
        "routes": [
            route object,
            ...
        ],
        "redirects": [
            redirect object,
            ...
        ],
    }
`}
	language="js"
/>

<p>
	All the objects are in the same format as the <a href="/docs/api-console">Console API</a>.
</p>

<ul>
	<li><a href="/docs/api-console#blog-object">Blog Object</a></li>
	<li><a href="/docs/api-console#language-object">Language Object</a></li>
	<li><a href="/docs/api-console#post-object">Post Object</a></li>
	<li><a href="/docs/api-console#user-object">User Object</a></li>
	<li><a href="/docs/api-console#media-object">Media Object</a></li>
	<li><a href="/docs/api-console#tag-object">Tag Object</a></li>
	<li><a href="/docs/api-console#navigation-object">Navigation Object</a></li>
	<li><a href="/docs/api-console#route-object">Route Object</a></li>
	<li><a href="/docs/api-console#redirect-object">Redirect Object</a></li>
</ul>

<p>
	Note: The <code>variants</code> of the <strong>Post Object</strong> will have an additional
	<code>content_html</code> property with content converted into HTML.
</p>
