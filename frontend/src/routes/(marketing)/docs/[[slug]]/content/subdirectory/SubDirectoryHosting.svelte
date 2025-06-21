<script lang="ts">
	import { TableRow } from '@hyvor/design/components';
	import { Table } from '@hyvor/design/components';
	import { Divider } from '@hyvor/design/components';
	import { Text } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { Tag, Button } from '@hyvor/design/components';
</script>

<h1>Subdirectory Hosting</h1>

<p>
	According to most SEO experts, a subdirectory like <code>/blog</code> is the best place to host your
	blog. Hyvor Blogs supports multiple methods to host your blog in a subdirectory.
</p>

<h2 id="docker">1. Docker</h2>

<p>
	If you are already using Docker within your website, you can use our official Docker image to host
	your Hyvor Blog in a subdirectory. You can use a reverse proxy like Caddy, Nginx or Traefik to 
	route blog traffic to the Docker container.
</p>

<p>
	<Button
		href="https://hyvor.com/blog/docker-subdirectory-blog"
		as="a"
		color="input"
		style="text-decoration:none"
		target="_blank"
	>
		View blog post <IconBoxArrowUpRight slot="end" />
	</Button>
	<Text small light style="margin-left:5px">on hyvor.com/blog</Text>
</p>

<Divider color="var(--border)" margin={40} />

<h2 id="cloudflare-workers">
	2. Cloudflare Workers <Tag color="blue">#nocode</Tag>
</h2>

<p>
	If you are already using Cloudflare for your domain, using <a
		href="https://workers.cloudflare.com/"
		rel="nofollow"
		target="_blank">Cloudflare Workers</a
	> is by far the easiest method to host your blog in a subdirectory. You can set it up in a couple of
	minutes without writing a single line of code.
</p>

<p>
	<Button
		href="https://hyvor.com/blog/cloudflare-workers-blog"
		as="a"
		color="input"
		style="text-decoration:none"
		target="_blank"
	>
		View blog post <IconBoxArrowUpRight slot="end" />
	</Button>
	<Text small light style="margin-left:5px">on hyvor.com/blog</Text>
</p>

<Divider color="var(--border)" margin={40} />

<h2 id="web-frameworks">3. Web Frameworks</h2>

<p>
	If your main website is built with a web framework like NextJS or Laravel, you can use the same
	framework to host your blog. You can set up your blog to be served directly from the framework and
	use a cache (filesystem, redis, etc.) to improve performance.
</p>

<p>We currently have libraries for the following frameworks.</p>

<Table columns="1fr 1fr 1fr">
	<TableRow head>
		<div>Framework</div>
		<div>Blog Tutorial</div>
		<div>Package (Github)</div>
	</TableRow>
	<TableRow>
		<div>NextJS</div>
		<div>
			<a href="https://hyvor.com/blog/nextjs-blog" target="_blank">
				View <IconBoxArrowUpRight />
			</a>
		</div>
		<div>
			<a href="https://github.com/hyvor/hyvor-blogs-serve-web" target="_blank">
				View <IconBoxArrowUpRight />
			</a>
		</div>
	</TableRow>
	<TableRow>
		<div>Laravel</div>
		<div>
			<a href="https://hyvor.com/blog/laravel-blog" target="_blank">
				View <IconBoxArrowUpRight />
			</a>
		</div>
		<div>
			<a href="https://github.com/hyvor/hyvor-blogs-laravel" target="_blank">
				View <IconBoxArrowUpRight />
			</a>
		</div>
	</TableRow>
	<TableRow>
		<div>Symfony</div>
		<div>
			<a href="https://hyvor.com/blog/symfony-blog" target="_blank">
				View <IconBoxArrowUpRight />
			</a>
		</div>
		<div>
			<a href="https://github.com/hyvor/hyvor-blogs-symfony" target="_blank">
				View <IconBoxArrowUpRight />
			</a>
		</div>
	</TableRow>
</Table>

<p>
	If your framework is not support, you can manually handle the requests and serve the blog from
	your framework using:
</p>

<ul>
	<li>
		<a href="/docs/api-delivery"> Delivery API </a> - Learn how to serve a request from your framework.
	</li>
	<li>
		<a href="/docs/webhooks"> Webhooks </a> - Use to invalidate cache when the blog is updated.
	</li>
</ul>

<p>
	We recommend you to check out the code in the above packages to get an idea of how to serve the
	blog from your framework. If you need any help, please contact us.
</p>

<Divider color="var(--border)" margin={40} />

<h2 id="reverse-proxy">4. Reverse Proxy</h2>

<p>
	If you are using a web server like Nginx or Caddy, you can use a reverse proxy to serve the blog
	from a subdirectory.
</p>

<Table columns="1fr 1fr">
	<TableRow head>
		<div>Server</div>
		<div>Blog Tutorial</div>
	</TableRow>
	<TableRow>
		<div>Caddy</div>
		<div>
			<a href="https://hyvor.com/blog/caddy-blog" target="_blank">
				View <IconBoxArrowUpRight />
			</a>
		</div>
	</TableRow>
	<TableRow>
		<div>Nginx</div>
		<div>Coming soon</div>
	</TableRow>
</Table>
