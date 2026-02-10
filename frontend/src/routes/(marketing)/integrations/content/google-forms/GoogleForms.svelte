<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import { Docs, DocsImage } from '@hyvor/design/marketing';
	import copyCodeImg from './copy-code.png';
	import sendFormImg from './send-form.png';
	import customCodeImg from './custom-code.png';
	import footerImg from './footer.png';
	import pasteCodeImg from './paste-code.png';
	import finalImg from './final.png';
</script>

<div class="guide-content">
	<Callout type="info">
		<p><b>Requirements</b>:</p>
		<ul>
			<li>A blog created using Hyvor Blogs</li>
			<li>A Google account to use Google Forms</li>
			<li>A form created using Google Forms</li>
		</ul>
	</Callout>

	<h2 id="copy-the-embed-code">Copy the Embed Code</h2>
	<ol>
		<li>Go to the Google form you have created</li>
		<li>Click on the <b>Send</b> button</li>
		<DocsImage src={sendFormImg} alt="Send form" />
		<li>Click on the <b>Embed</b> icon</li>
		<li>Copy the embed code</li>
		<DocsImage src={copyCodeImg} alt="Copy code" />
	</ol>

	<h2 id="add-the-embed-code-to-your-blog">Add the Embed Code to Your Blog</h2>
	<p>If you want to place the form on a blog/article page,</p>
	<ol>
		<li>Go to the Hyvor Blogs console</li>
		<li>Go to <b>Posts/Pages</b> and open the post/page you want to add the form</li>
		<DocsImage src={customCodeImg} alt="Custom code" />
		<li>Type <b>/</b> and choose <b>Custom HTML/Twig</b></li>
		<li>Paste the copied code and press <b>Enter</b></li>
		<DocsImage src={pasteCodeImg} alt="Paste code" />
	</ol>

	<p>
		If you want to place the form on one of the main pages of the blog (ex: index page, contact
		us page, about us page, footer, etc). For demonstration, the form is placed on the footer.
	</p>

	<ol>
		<li>Go to the Hyvor Blogs console</li>
		<li>Go to <b>Themes &rarr;</b><b>Templates &rarr;</b><b><code>_footer.twig</code></b></li>
		<li>Paste the copied code where you want to place the form and click <b>Save</b>.</li>
		<DocsImage src={footerImg} alt="Footer" />
	</ol>

	<p>That's it! You have successfully added a Google Form to your blog.</p>
	<DocsImage src={finalImg} height={800} alt="Final" />
</div>
