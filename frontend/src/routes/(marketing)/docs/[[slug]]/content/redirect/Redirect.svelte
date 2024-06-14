<script>
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout } from '@hyvor/design/components';
	import { IconExclamationOctagonFill } from '@hyvor/icons';

	import createRedirectImg from './create-redirect.png';
	import example1Img from './dynamic-redirect.png';
</script>

<h1>Redirects</h1>

<p>
	Redirects are used to redirect a visitor from one path within your blog to a different URL. You
	can configure redirects in <strong>Settings &rarr; Redirects</strong> in the Console.
</p>

<DocsImage src={createRedirectImg} alt="Create Redirect" />

<ul>
	<li>
		<strong>Dynamic</strong> - Match patterns using regular expressions. See
		<a href="#dynamic">Dynamic Redirects</a>.
	</li>
	<li>
		<strong>From</strong> - The path you want to redirect from. For example, <code>/path</code>. For
		dynamic redirects, you can use regular expressions.
	</li>
	<li>
		<strong>To</strong> - Where to redirect the visitor. It can be a path within your blog or an external
		URL.
	</li>
	<li>
		<strong>Type</strong> - Permanent (HTTP 301) or Temporary (HTTP 302). It determines the
		<a
			href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#redirection_messages"
			rel="nofollow"
			target="_blank">HTTP response code</a
		>.
	</li>
</ul>

<h2 id="dynamic">Dynamic Redirects</h2>

<p>
	With dynamic redirects, you can match a path dynamically using a pattern. This is useful when you
	want to redirect multiple paths that follow a pattern to a single destination. For example, you
	can redirect all requests starting with <code>/author/</code> to an external site.
</p>

<Callout type="info">
	<IconExclamationOctagonFill slot="icon" />
	You can only have up to 5 dynamic redirects per blog.
</Callout>

<DocsImage src={example1Img} alt="Example 1" />

<p>Here's how to create a dynamic redirect:</p>

<ul>
	<li>
		Turn on <strong>Dynamic</strong> option
	</li>
	<li>
		Set <strong>From</strong> to a regular expression you want to match (we support PCRE2 syntax).
	</li>
	<li>
		Set <strong>To</strong> to the URL you want to redirect to. You can use captured groups here,
		like <code>$1</code>.
	</li>
	<li>
		Choose the <strong>Type</strong> of redirect and click <strong>Add</strong>.
	</li>
</ul>

<h3 id="dynamic-examples">Dynamic Redirect Examples</h3>

<p>
	1. To redirect all requests starting with <code>/author/</code> to an external site:
</p>

<ul>
	<li>
		<strong>From:</strong> <code>/author/(.*)</code>
	</li>
	<li>
		<strong>To:</strong> <code>https://externalsite.com</code>
	</li>
</ul>

<p>
	2. To redirect all requests starting with <code>/author/</code> to an external site, keeping the rest
	of the path:
</p>

<ul>
	<li>
		<strong>From:</strong> <code>/author/(.*)</code>
	</li>
	<li>
		<strong>To:</strong> <code>https://externalsite.com/$1</code>
	</li>
</ul>

<p>
	3. To redirect all requests starting with <code>/author/</code> followed by another
	<code>/</code> to an external site, with some changes in the original structure of the path:
</p>

<ul>
	<li>
		<strong>From:</strong> <code>/author/([^/]+)/(.*)</code>
	</li>
	<li>
		<strong>To:</strong> <code>https://externalsite.com/$1/somedirectory/$2</code>
	</li>
</ul>

<p>
	4. Redirect paths ending with <code>/</code> to the same path without the trailing slash:
</p>

<ul>
	<li>
		<strong>From:</strong> <code>/(.*)/$</code>
	</li>
	<li>
		<strong>To:</strong> <code>https://yourblog.com/$1</code>
	</li>
</ul>

<p>If you need help with regular expressions, feel free to contact support.</p>
