<script>
	import { DocsImage } from "@hyvor/design/marketing";

    import createRedirectImg from './create-redirect.png';
</script>
<h1>Redirects</h1>

<p>
    Redirects are used to redirect a visitor from one path within your blog to another path or an external URL. You can configure redirects in <strong>Settings &rarr; Redirects</strong> in the Console.
</p>

<DocsImage src={createRedirectImg} alt="Create Redirect" />

<ul>
    <li>
        <strong>From</strong> - The path you want to redirect from. For example, <code>/path</code>. Currently, we do not support wildcards or regex in the match.
    </li>
    <li>
        <strong>To</strong> - Where to redirect the visitor. It can be a path within your blog or an external URL.
    </li>
    <li>
        <strong>Type</strong> - Permanent (HTTP 301) or Temporary (HTTP 302). It determines the <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#redirection_messages" rel="nofollow" target="_blank">HTTP response code</a>.
    </li>
</ul>