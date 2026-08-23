<script>
	import { CodeBlock, Table, TableRow } from '@hyvor/design/components';
</script>

<h1>Delivery API</h1>

<p>
	Delivery API tells you information about how to "serve a request". This API is the backbone of <a
		href="/docs/subdirectory">hosting a blog on a subdirectory</a
	>.
</p>

<p>
	<strong>API Endpoint</strong>:
	<code>https://blogs.hyvor.com/api/delivery/v0/{`{subdomain}`}</code>
</p>

<p>
	Replace <code>{`{subdomain}`}</code> with the subdomain of your blog.
</p>

<h2 id="request">Request</h2>

<ul>
	<li>
		<code>GET</code> request to the API endpoint
	</li>
	<li>
		Set <code>api_key</code> query parameter to a valid Delivery API key. You can create one at
		<strong>Settings &rarr; API Keys</strong>.
	</li>
	<li>
		Set <code>path</code> query parameter to the path of the blog you want to get information about.
		For example, if you want to get information about
		<code>https://myblog.com/hello-world</code>, set <code>path</code> to
		<code>/hello-world/</code>.
	</li>
</ul>

<h2 id="response">Response</h2>

<p>A successful response will be one of the following JSON objects:</p>

<h3 id="file">1. File</h3>

<p>This object is returned when the path is a file.</p>

<CodeBlock
	code={`
    {
        "type": "file",
        "at": 1661590503,
        "cache": true,
        "status": 200,
        "file_type": "template",
        "content": "SGVsbG8gV29ybGQ=",
        "mime_type": "text/html",
        "cache_control": "no-cache, private"
    }
`}
	language="json"
/>

<h3 id="redirect">2. Redirect</h3>

<p>This object is returned when the path is a redirect.</p>

<CodeBlock
	code={`
    {
        "type": "redirect",
        "at": 1661590503,
        "cache": true,
        "status": 301,
        "to": "https://example.com"
    }
`}
	language="json"
/>

<p>Common properties:</p>

<Table columns="1fr 1fr 2fr">
	<TableRow head>
		<div>Key</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>
	<TableRow>
		<div><code>type</code></div>
		<div><code>string</code></div>
		<div>
			<code>file</code> or <code>redirect</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>at</code></div>
		<div><code>integer</code></div>
		<div>UNIX timestamp when the object was created</div>
	</TableRow>
	<TableRow>
		<div><code>cache</code></div>
		<div><code>boolean</code></div>
		<div>
			Whether the response object should be cached in proxy/intermediary servers. <code>false</code> for
			post preview routes.
		</div>
	</TableRow>
	<TableRow>
		<div><code>status</code></div>
		<div><code>integer</code></div>
		<div>
			HTTP status code of the response. Can be <code>200</code>, <code>301</code>,
			<code>302</code>, or <code>404</code>.
		</div>
	</TableRow>
</Table>

<p>File properties:</p>

<Table columns="1fr 1fr 2fr">
	<TableRow head>
		<div>Key</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>
	<TableRow>
		<div><code>file_type</code></div>
		<div><code>string</code></div>
		<div>
			<code>template</code>, <code>asset</code>, or <code>media</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>content</code></div>
		<div><code>string</code></div>
		<div>Base64 encoded content of the file</div>
	</TableRow>
	<TableRow>
		<div><code>mime_type</code></div>
		<div><code>string</code></div>
		<div>Mime Type of the file (For Content-Type header)</div>
	</TableRow>
	<TableRow>
		<div><code>cache_control</code></div>
		<div><code>string</code></div>
		<div>HTTP Cache-Control header value</div>
	</TableRow>
</Table>

<p>Redirect properties:</p>

<Table columns="1fr 1fr 2fr">
	<TableRow head>
		<div>Key</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>
	<TableRow>
		<div><code>to</code></div>
		<div><code>string</code></div>
		<div>URL to redirect to</div>
	</TableRow>
</Table>
