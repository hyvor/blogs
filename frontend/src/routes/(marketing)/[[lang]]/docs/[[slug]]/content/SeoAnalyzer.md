<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout } from '@hyvor/design/components';
</script>

# SEO Analyzer

The SEO analysis tool in the post editor will give you suggestions to improve your post's SEO. It works based on pre-defined rules.

<Callout type="info" title="Disclaimer">
    	SEO analysis is <b>merely a suggestion</b>. Getting a higher score alone will not make your
    	posts rank high. There are also other factors that affect your SEO, such as backlinks, domain
    	authority, etc. However, these suggestions will help you make less SEO mistakes.
</Callout>


<DocsImage src="/images/docs/writing/seo.png" alt="SEO Analysis" width={400} />

<br />

To start analyzing your post, add a primary keyword for your post. You can also add secondary keywords. Hyvor Blogs will then analyze your post content and metadata and give you suggestions in real-time to improve SEO for your post.

These are the tests that Hyvor Blogs will run on your post:

<ul>
	<li><b>Primary keyword in the title</b></li>
	<ul>
		<li>100% if the primary keyword is at the beginning of the title</li>
		<li>75% if the primary keyword is in the first 50 characters of the title</li>
		<li>49% if the primary keyword is after the first 50 characters of the title</li>
		<li>0% if the primary keyword is not in the title</li>
	</ul>

    <li><b>Primary keyword in the description</b></li>
    <li><b>Primary keyword in the slug</b></li>
    <p>
    	If the primary keyword is <code>blogging platforms</code>, we check for
    	<code>blogging-platforms</code>
    	in the slug. It is recommended to set a <b>short slug with hyphens</b>. In this case, the score
    	will be:
    </p>
    <ul>
    	<li>100% if the slug matches exactly <code>blogging-platforms</code></li>
    	<li>75% if the slug contains <code>blogging-platforms</code> with other words</li>
    </ul>

    <li><b>Primary keyword in the beginning of the content</b></li>
    <p>
    	If your content is longer than 300 words, the primary keyword should be in the first 10% of the
    	content. If it is shorter than 300 words, it should be somewhere in the content.
    </p>

    <li><b>Content length</b></li>
    <p>(The best content length depends on the topic, which is not considered here)</p>
    <ul>
    	<li>0% for less than 400 words</li>
    	<li>1% for each 25 words after 400 words (2500+ words = 100%)</li>
    </ul>

    <li><b>All keywords in the content</b></li>
    <p>All keywords should be present in the post content.</p>

    <li><b>All keywords in subheadings</b></li>
    <p>Each keyword should be present in at least one subheading (h2, h3, h4, h5, h6).</p>

    <li><b>All keywords in image alt attributes</b></li>
    <p>Each keyword should be present in at least one image alt attribute.</p>

    <li><b>Keyword density</b></li>
    <p>Checks for keyword density in content (<code>keywords count / total words</code>).</p>
    <ul>
    	<li>0% for less than 0.1%</li>
    	<li>50% for 0.1% to 0.5%</li>
    	<li>100% for 0.5% to 2.5%</li>
    	<li>50% for 2.5% to 5%</li>
    	<li>0% for more than 5%</li>
    </ul>

    <li><b>Slug length</b></li>
    <p>Shorter slugs are better. This test will pass if the slug is less than 50 characters.</p>

    <li><b>External links</b></li>
    <p>At least one external link should be present in the post.</p>

    <li><b>Internal links</b></li>
    <p>
    	At least one internal link should be present in the post. Links to any subdomain of your main
    	domain will be considered as internal links. See <a href="/docs/link-analyzer#link-types"
    		>link types</a
    	>
    	for more information. <code>internal-blog</code>, <code>internal-domain</code>, and
    	<code>internal-root-domain</code> links are considered as internal links.
    </p>

    <li><b>Images</b></li>
    <ul>
    	<li>70% - 1 image</li>
    	<li>80% - 2 images</li>
    	<li>90% - 3 images</li>
    	<li>100% - 4 or more images</li>
    </ul>

    <li><b>All images have alt attributes</b></li>
    <p>All images should have alt attributes</p>

</ul>