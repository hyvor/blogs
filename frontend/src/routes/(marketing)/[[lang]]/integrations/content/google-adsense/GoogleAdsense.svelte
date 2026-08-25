<script lang="ts">
	import { Callout, CodeBlock } from '@hyvor/design/components';
	import { Docs, DocsImage } from '@hyvor/design/marketing';
</script>

<div class="guide-content">
	<Callout type="info">
		<p><b>Requirements</b>:</p>
		<ul>
			<li>
				a Google AdSense account. If you don't have one, you can create one from <a
					href="https://www.google.com/adsense/start/"
					target="_blank">here</a
				>
			</li>
			<li>a blog on <a href="/console">Hyvor Blogs</a></li>
		</ul>
	</Callout>

	<h2 id="">Step 1 - Add your blog to Google AdSense</h2>
	<ol>
		<li>On the AdSense Dashboard, go to <b>Site &rarr; New site</b>.</li>
		<li>Enter your blog's URL and click <b>Save</b>.</li>
		<DocsImage
			src="/images/integrations/content/google-adsense/adding-site.png"
			alt="Adding site to Google AdSense"
		/>
	</ol>

	<p>There are two types of connecting your blog. Using,</p>
	<ul>
		<li>Adsense code snippet</li>
		<li>Ads.txt snippet</li>
	</ul>

	<h3 id="using-adsense-code-snippet">1.1 Using AdSense Code Snippet</h3>
	<DocsImage src="/images/integrations/content/google-adsense/code-snippet.png" alt="Paste code" />
	<ol>
		<li>Copy the code snippet given.</li>
		<li>
			Go to <b>Settings &rarr; Custome Code &rarr; Head Code</b> in your blog's dashboard.
		</li>
		<DocsImage src="/images/integrations/content/google-adsense/paste-code.png" alt="Paste code" />
		<li>On the AdSense page, check “<b>I've placed the code</b>” and click <b>Verify</b>.</li>
		<li>Click on <b>Request Review</b>.</li>
	</ol>

	<h3 id="using-ads-txt-snippet">1.2 Using Ads.txt Snippet</h3>
	<ol>
		<li>Copy the code snippet given.</li>
		<DocsImage src="/images/integrations/content/google-adsense/ads-txt.png" alt="Ads.txt" />
		<li>
			Go to <b>Themes &rarr; Templates &rarr; + New</b> on your blog's dashboard and create a file
			named <code>route-ads.txt.twig</code>.
		</li>
		<li>Paste the code snippet in that twig file and click <b>Save</b>.</li>
		<Callout type="info">
			Tip: You can check Ads.txt which should appear at <code>yourwebsite.com/ads.txt</code>
		</Callout>
		<li>On the AdSense page, check “<b>I've placed the code</b>” and click <b>Verify</b>.</li>
		<DocsImage
			src="/images/integrations/content/google-adsense/ads-txt-add.png"
			alt="Ads.txt add"
		/>
		<li>Click on <b>Request Review</b>.</li>
	</ol>

	<p>Wait for Adsense approval.</p>

	<h2 id="adding-ads">Step 2 - Adding Ads to your Blog</h2>
	<p>After you created ads,</p>
	<ol>
		<li>Copy the code snippet you get just after you created an ad.</li>
		<DocsImage
			src="/images/integrations/content/google-adsense/copy-ad-code.png"
			alt="Copy ad code"
		/>
		<li>Go to your "<b>Themes</b>" on blog console.</li>
		<li>Choose the page type you want to add your ads to (for example we use the homepage):</li>
		<ul>
			<li><code>index.twig</code> - homepage</li>
			<li><code>post.twig</code> - single post page</li>
		</ul>

		<li>
			Paste the code snippet in the twig file based on where you want to place ads and click <b
				>Save</b
			>.
		</li>
		<DocsImage
			src="/images/integrations/content/google-adsense/paste-ad-code.png"
			alt="Paste ad code"
		/>
	</ol>
</div>
