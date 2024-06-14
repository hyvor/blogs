<script>
	import { DocsImage } from "@hyvor/design/marketing";

    import createRedirectImg from './create-redirect.png';
    import example1Img from './example-1.png';
</script>
<h1>Redirects</h1>

<p>
    Redirects are used to redirect a visitor from one path within your blog to another path or an external URL. You can configure redirects in <strong>Settings &rarr; Redirects</strong> in the Console.
</p>

<DocsImage src={createRedirectImg} alt="Create Redirect" />

<ul>
    <li>
        <strong>Dynamic</strong> - Choose whether you need a simple redirect or a dynamic redirect where you match paths using regular expressions.
    </li>
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

<h2 id='dynamic'>Dynamic Redirects</h2>

<p>
    Dynamic redirects allows to identify patterns and re-route requests in a flexible and automated manner. This approach contrasts with static redirects, which require specifying exact source and destination URLs. Dynamic behavior in redirects is primarily achieved through the use of regular expressions (regex), which allow for the matching of URL patterns instead of fixed URLs.
</p>

<h3 id='how-it-works'>How it works</h3>

<ul>
    <li>
        Turn on <strong>Dynamic Redirects</strong>
    </li>
    <li>
        Specify the dynamic <strong>From</strong> slug using regular expressions. Note that we support <code>PCRE2</code> regex syntax. 
        (You can test your regex patterns using <a href="https://regex101.com/" rel="nofollow" target="_blank">regex101.com</a>.)
    </li>
    <li>
        Specify the <strong>To</strong> slug. You can use the captured groups in the <strong>From</strong> slug in the <strong>To</strong> slug.
    </li>
    <li>
        Save the redirect.
    </li>
</ul>

<h3 id='examples'>Examples</h3>

<h4>Example 1</h4>

<DocsImage src={example1Img} alt="Example 1" />

<p>
    In the above example, the regex <code>/slug/(.*)</code> matches any path that starts with <code>/slug</code> and captures the rest of the path. 
    The captured path is then used in the redirect destination as <code>https://redirecthere.com/rest-of-the-path</code>, allowing for dynamic redirection based on the original URL.

</p>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<p>
    For example, you can use the following regex to match all paths that start with <code>/blog</code> and redirect them to <code>/posts</code>:
</p>

<pre>
    <code>
        ^/blog/(.*)$
    </code>
</pre>

<p>
    In the above example, the regex <code>^/blog/(.*)$</code> matches any path that starts with <code>/blog</code> and captures the rest of the path. The captured path is then used in the redirect destination, allowing for dynamic redirection based on the original URL.
</p>

<p>
    Regular expressions can be complex and powerful tools for matching patterns in URLs. However, they can also be challenging to write and debug. If you are unfamiliar with regular expressions, we recommend consulting resources such as <a href="https://regex101.com/" rel="nofollow" target="_blank">regex101.com</a> to test and refine your regex patterns.
</p>

