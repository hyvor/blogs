<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
</script>

# Delivery API

Delivery API tells you information about how to "serve a request". This API is the backbone of [hosting a blog on a subdirectory](/docs/subdirectory).

**API Endpoint**: `https://blogs.hyvor.com/api/delivery/v0/{subdomain}`

Replace `{subdomain}` with the subdomain of your blog.

<h2 id="request">Request</h2>

- `GET` request to the API endpoint
- Set `api_key` query parameter to a valid Delivery API key. You can create one at **Settings &rarr; API Keys**.
- Set `path` query parameter to the path of the blog you want to get information about. For example, if you want to get information about `https://myblog.com/hello-world`, set `path` to `/hello-world/`.

<h2 id="response">Response</h2>

A successful response will be one of the following JSON objects:

<h3 id="file">1. File</h3>

This object is returned when the path is a file.

```json
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
```

<h3 id="redirect">2. Redirect</h3>

This object is returned when the path is a redirect.

```json
{
	"type": "redirect",
	"at": 1661590503,
	"cache": true,
	"status": 301,
	"to": "https://example.com"
}
```

Common properties:

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

File properties:

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

Redirect properties:

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
