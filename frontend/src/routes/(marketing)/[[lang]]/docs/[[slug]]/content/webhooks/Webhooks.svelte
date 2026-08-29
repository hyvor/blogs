<script>
	import { Callout, CodeBlock, Link, Table, TableRow, Text } from '@hyvor/design/components';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';
</script>

<h1>Webhooks</h1>

<p>Webhooks are a way to get notified when an event happens on your blog.</p>

<ul>
	<li>A blog can have up to 5 webhooks</li>
	<li>Each webhook can subscribe to one or more events</li>
</ul>

<Table columns="1fr 2fr 1fr">
	<TableRow head>
		<div>Event</div>
		<div>Dispatched</div>
		<div>Data</div>
	</TableRow>
	<TableRow>
		<div><code>blog.updated</code></div>
		<div>Any setting of the blog is updated</div>
		<div>
			<code>{'{ blog: '}<a href="/docs/api-console#blog-object">Blog</a>{' }'}</code>
		</div>
	</TableRow>

	<div class="separator"></div>

	<div class="title">Post</div>

	<TableRow>
		<div><code>post.created</code></div>
		<div>A new post is created</div>
		<div>
			<code>{'{ post: '}<a href="/docs/api-console#post-object">Post</a>{' }'}</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>post.updated</code></div>
		<div>A post is updated</div>
		<div>
			<code>{'{ post: '}<a href="/docs/api-console#post-object">Post</a>{' }'}</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>post.deleted</code></div>
		<div>A post is deleted</div>
		<div>
			<code>{'{ post: '}<a href="/docs/api-console#post-object">Post</a>{' }'}</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>post.variant.published</code></div>
		<div>A post variant is published</div>
		<div>
			<code>{'{ post: '}<a href="/docs/api-console#post-object">Post</a>{' }'}</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>post.variant.unpublished</code></div>
		<div>A post variant is unpublished</div>
		<div>
			<code>{'{ post: '}<a href="/docs/api-console#post-object">Post</a>{' }'}</code>
		</div>
	</TableRow>

	<div class="separator"></div>

	<div class="title">Tags</div>

	<!-- All with tag. -->

	<TableRow>
		<div><code>tag.created</code></div>
		<div>A new tag is created</div>
		<div>
			<code>{'{ tag: '}<a href="/docs/api-console#tag-object">Tag</a>{' }'}</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>tag.updated</code></div>
		<div>A tag is updated</div>
		<div>
			<code>{'{ tag: '}<a href="/docs/api-console#tag-object">Tag</a>{' }'}</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>tag.deleted</code></div>
		<div>A tag is deleted</div>
		<div>
			<code>{'{ tag: '}<a href="/docs/api-console#tag-object">Tag</a>{' }'}</code>
		</div>
	</TableRow>

	<div class="separator"></div>

	<!-- All tag events with users -->

	<div class="title">Users</div>

	<TableRow>
		<div><code>user.created</code></div>
		<div>A new user is created</div>
		<div>
			<code>{'{ user: '}<a href="/docs/api-console#user-object">User</a>{' }'}</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>user.updated</code></div>
		<div>A user is updated</div>
		<div>
			<code>{'{ user: '}<a href="/docs/api-console#user-object">User</a>{' }'}</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>user.deleted</code></div>
		<div>A user is deleted</div>
		<div>
			<code>{'{ user: '}<a href="/docs/api-console#user-object">User</a>{' }'}</code>
		</div>
	</TableRow>

	<div class="separator"></div>
	<div class="title">Media</div>

	<TableRow>
		<div><code>media.created</code></div>
		<div>A media item is added</div>
		<div>
			<code>{'{ media: '}<a href="/docs/api-console#media-object">Media</a>{' }'}</code>
		</div>
	</TableRow>

	<TableRow>
		<div><code>media.deleted</code></div>
		<div>A media item is deleted</div>
		<div>
			<code>{'{ media: '}<a href="/docs/api-console#media-object">Media</a>{' }'}</code>
		</div>
	</TableRow>

	<div class="separator"></div>

	<div class="title">Other</div>

	<TableRow>
		<div><code>navigation.changed</code></div>
		<div><a href="/docs/navigation-links">Blog navigation</a> changed</div>
		<div>
			<code
				>{'{ navigation: '}<a href="/docs/api-console#navigation-object">Navigation</a
				>{'[] }'}</code
			>
		</div>
	</TableRow>

	<TableRow>
		<div><code>routes.changed</code></div>
		<div><a href="/docs/routes">Blog routes</a> changed</div>
		<div>
			<code>{'{ routes: '}<a href="/docs/api-console#route-object">Route</a>{'[] }'}</code>
		</div>
	</TableRow>

	<TableRow>
		<div><code>languages.changed</code></div>
		<div><a href="/docs/languages">Blog languages</a> changed</div>
		<div>
			<code>{'{ languages: '}<a href="/docs/api-console#language-object">Language</a>{'[] }'}</code>
		</div>
	</TableRow>

	<div class="separator"></div>
	<div class="title">Cache Clearing</div>

	<TableRow>
		<div><code>cache.single</code></div>
		<div>When cache of a single path should be cleared (styles.css, assets, media, etc.)</div>
		<div>
			<code>{`{ path: string }`}</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>cache.templates</code></div>
		<div>
			When cache of all template-generated paths should be cleared (index, posts, feeds, etc.)
		</div>
		<div>
			<Text small light>Empty object</Text>
		</div>
	</TableRow>
	<TableRow>
		<div><code>cache.all</code></div>
		<div>When all cache should be cleared</div>
		<div>
			<Text small light>Empty object</Text>
		</div>
	</TableRow>
</Table>

<h2 id="request">Webhook Request</h2>

<p>
	When an event happens, a <strong>POST</strong> request is sent to the webhook URL. The request
	body is a JSON object with the following properties. The <code>content</code> property is the event
	data. See the above table for the event data structure.
</p>

<CodeBlock
	code={`
    {
        "subdomain": "my-subdomain",
        "timestamp": 1645208678,
        "event": "cache.single",
        "data": {}
    }
`}
	language="json"
/>

<h2 id="security">Security</h2>

<p>
	Each webhook request includes an <code>X-Signature</code> header containing an HMAC-SHA256 signature
	of the request body, signed with your webhook's secret key. You can find the secret in the Console under
	your webhook settings.
</p>

<p>To verify the signature:</p>

<CodeBlock
	code={`
    const signature = request.headers['x-signature'];
    const expected = crypto
        .createHmac('sha256', env.HB_WEBHOOK_SECRET)
        .update(JSON.stringify(request.body))
        .digest('hex');

    if (signature !== expected) {
        return "Unauthorized";
    }
`}
	language="js"
/>

<h2 id="response">Response & Retries</h2>

<p>
	We expect a <strong>200 HTTP Response Code</strong> from your server to mark the webhook as success.
	If we get any other response code or fail to reach your servers, we will retry to send the webhook for
	3 more times after
</p>

<ul>
	<li>1 minute</li>
	<li>5 minutes</li>
	<li>30 minutes</li>
</ul>

<p>
	If all fail, we will mark that webhook as failed and will no longer send it automatically.
	However, you can manually trigger it from the Console later.
</p>

<style>
	.separator {
		margin: 15px 0;
		border-bottom: 1px solid var(--border);
	}
	.title {
		padding: 0 16px;
		font-weight: 600;
		margin-bottom: 8px;
	}
</style>
