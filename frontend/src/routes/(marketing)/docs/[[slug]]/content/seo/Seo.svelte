<script>
	import { CodeBlock, Table, TableRow } from "@hyvor/design/components";
	import { DocsImage } from "@hyvor/design/marketing";

    import canonicalImg from './canonical-setting.png';
</script>
<h1>SEO</h1>

<p>
    Hyvor Blogs handles technical SEO for you, allowing you to focus on what matters the most - writing. Some of the SEO features can be configured in <strong>Console &rarr; Settings &rarr; SEO</strong>.
</p>

<h2 id="meta">
    Meta Tags
</h2>

<p>
    Hyvor Blogs automatically meta tags to your blog. They help search engines to understand your blog better and social media sites to display your blog better.
</p>

<h3 id="basic">
    Basic Meta Tags
</h3>

<p>
    These are the basic meta tags that are added to all pages in your blog.
</p>

<CodeBlock code={`
    <title>My Blog</title>
    <meta name="description" content="My Blog Description" />
    <link rel="canonical" href="https://myblog.hyvorblogs.io" />
`} />

<h3 id="language-variants">
    Language Variants
</h3>

<p>
    If you have <a href="/docs/languages">set up multiple languages</a>, Hyvor Blogs will automatically add <code>hreflang</code> tags to index and post pages.
</p>

<CodeBlock code={`
    <link rel="alternate" href="https://myblog.hyvorblogs.io/fr" hreflang="fr" />
    <link rel="alternate" href="https://myblog.hyvorblogs.io/es" hreflang="es" />
`} />

<h3 id="facebook-twitter-tags">
    Facebook and Twitter Tags
</h3>

<p>
    These tags help social media sites to generate rich previews of your blog and posts.
</p>

<CodeBlock code={`
    <!-- FACEBOOK (OG) -->
    <meta property="og:site_name" />
    <meta property="og:type" />
    <meta property="og:title" />
    <meta property="og:locale" />
    <meta property="og:description" />
    <meta property="og:url" />
    <meta property="og:image" />
    <!-- For Posts -->
    <meta property="article:published_time" />
    <meta property="article:modified_time" />
    <meta property="article:author" />  <!-- Authors -->
    <meta property="article:author" /> 
    <meta property="article:section" />  <!-- Tags -->
    <meta property="article:section" />

    <!-- TWITTER -->
    <meta name="twitter:card" />
    <meta name="twitter:title" />
    <meta name="twitter:description" />
    <meta name="twitter:url" />
    <meta name="twitter:image" />
    <meta name="twitter:site" /> <!-- only if Twitter URL is set in blog settings -->
    <meta name="twitter:creator" /> <!-- only if Twitter URL is set for the primary author -->
`} />


<h2 id="rich-schema">
    Rich Schema
</h2>

<p>
    Rich schema support is coming very soon!
</p>


<h2 id="canonical">
    Canonical URLs
</h2>

<p>
    Hyvor Blogs follows one principle when it comes to canonical URLs: <strong>One URL for one content</strong>. A post will be accessible from only one URL. This basic principle helps you avoid common duplicate content issues and helps search engines to understand your blog better.
</p>


<h3 id="custom-post-canonical">
    Custom Post Canonical URLs
</h3>

<p>
    In some cases, you may want to published a post published somewhere else on your blog. In such cases, you can set a custom canonical URL for the post in the post editor.
</p>

<DocsImage src={canonicalImg} alt="Canonical URL Setting" style="max-height:400px" />


<h2 id="robots">
    Robots.txt
</h2>

<p>
    Robots.txt is a file that tells search engine crawlers what pages to access and not. Hyvor Blogs comes with a default robots.txt, which should be sufficient for most blogs.
</p>

<CodeBlock code={`
    User-agent: *
    Sitemap: {{ _blog.base_url }}/sitemap.xml
    Disallow: /p/
`} />

<p>
    Default robots.txt file is as follows:
</p>

<ul>
    <li>
        <code>User-agent: *</code> - allows all crawlers to access your blog.
    </li>
    <li>
        <code>Sitemap: {`{{ _blog.base_url }}`}/sitemap.xml</code> - tells crawlers where to find the sitemap.
    </li>
    <li>
        <code>Disallow: /p/</code> - prevents crawlers from accessing /p/ routes (post preview pages).
    </li>
</ul>

<p>
    You can update your robots.txt file in <strong>Settings &rarr; SEO &rarr; Robots.txt</strong>. You can also use <a href="/docs/themes-templates#variables">theme variables</a> there (ex: <code>{`{{ _blog.base_url }}`}</code> in the default robots.txt file).
</p>

<h2 id="sitemap">
    Sitemap
</h2>

<p>
    Hyvor Blogs generates sitemaps automatically. You can find the <a href="https://www.sitemaps.org/protocol.html#index" target="_blank" rel="nofollow">sitemap index</a> at <code>/sitemap.xml</code> of your blog. You may submit this URL to search engines to help them discover your blog faster.
</p>

<p>
    Sitemap index format:
</p>

<CodeBlock code={`
    <?xml version="1.0" encoding="UTF-8"?>
    <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        <sitemap><loc>https://blog.hyvorblogs.io/sitemap-pages.xml</loc></sitemap>
        <sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-1.xml</loc></sitemap>
        <sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-2.xml</loc></sitemap>
    </sitemapindex>
`} />

<p>
    The sitemap index links to other sitemaps of the blog.
</p>

<ul>
    <li>
        <code>sitemap-pages.xml</code> - contains links to the pages and homepage.
    </li>
    <li>
        <code>sitemap-posts-[index].xml</code> -  contains links to posts. Each file can have up to 2500 URLs. First page has the oldest URLs. Within the file, we also auto generate
        <ul>
            <li>
                <code>{`<image:image>`}</code> to link to images in the post (only directly uploaded images)
            </li>
            <li>
                <code>{`<xhtml:link>`}</code> to link to language variants of the post
            </li>
        </ul>
    </li>
</ul>

<p>
    Here is an example <code>sitemap-posts-[index].xml</code>.
</p>

<CodeBlock code={`
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset 
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
        <url>
            <loc>https://blog.hyvorblogs.io/hello-world</loc>

            <xhtml:link rel="alternate" hreflang="en" href="https://blog.hyvorblogs.io/hello-world" />
            <xhtml:link rel="alternate" hreflang="fr" href="https://blog.hyvorblogs.io/fr/hello-world" />

            <image:image><image:loc>https://blog.hyvorblogs.io/media/hello-world.png</image:loc></image:image>
        </url>
    </urlset>
`} />


<h2 id="prevent-indexing">
    Prevent Indexing
</h2>


<p>
    This is the meta tag you can use to prevent search engines from indexing a page.
</p>

<CodeBlock code={`
    <meta name="robots" content="noindex">
`} />

<h3 id="whole-blog">
    1. Whole blog
</h3>

<p>
    To prevent search engines from indexing your <strong>whole blog</strong>, turn off the <strong>Settings → SEO → Allow Indexing</strong> option. Hyvor Blogs will add the above meta tag to all pages. You can also add this meta tag manually to your theme or head <a href="/docs/custom-code">custom code</a>.
</p>

<h3 id="whole-blog">
    2. Specific post
</h3>

<p>
    To prevent a post from indexing, you can add the above meta tag to the post's head <a href="/docs/custom-code#post">custom code</a>.
</p>

<h3 id="posts-of-tag">
    3. Posts of a tag
</h3>

<p>
    Sometimes you may want to prevent indexing posts that has a specific tag (ex: <code>no-index</code>). In such cases, you can add the above meta tag to the <a href="/docs/custom-code#tag">tag's custom code</a>.
</p>