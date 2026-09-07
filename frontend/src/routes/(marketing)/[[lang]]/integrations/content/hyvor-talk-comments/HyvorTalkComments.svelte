<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

<div class="guide-content">
	<Callout type="info">
		<p><b>Requirements</b>:</p>
		<ul>
			<li>
				a <a href="https://hyvor.com/login">HYVOR account</a>. This is the same account you used to
				create a blog at Hyvor Blogs
			</li>
			<li>a <a href="/pricing">subscription</a> for Hyvor Blogs</li>
		</ul>
	</Callout>

	<ol>
		<li>Go to <a href="/console">Hyvor Blogs Console</a>.</li>
		<li>Go to <b>Integrations &rarr; Hyvor Talk</b>.</li>
		<li>Click on <b>Connect Hyvor Talk</b></li>
		<DocsImage
			src="/images/integrations/content/hyvor-talk-comments/connect-hyvortalk.png"
			alt="Connect Hyvor Talk"
		/>
		<p>
			Then it will generate a code snippet for you as shown above. There are two methods you can use
			to complete this integration.
		</p>
		<ol>
			<li>Click "<b>Add to "Comments Embed Code"</b>".</li>
			<li>
				Copy the code and paste it in "<b>Comments Embed Code</b>", then click <b>Save</b>.
			</li>
		</ol>
		<DocsImage
			src="/images/integrations/content/hyvor-talk-comments/paste-code.png"
			alt="Paste Code"
		/>

		<p>
			That's it! You have successfully connected Hyvor Talk to your Hyvor Blogs. Now, you can manage
			your comments from the <a href="https://talk.hyvor.com/console">Hyvor Talk dashboard</a>.
		</p>
		<DocsImage
			src="/images/integrations/content/hyvor-talk-comments/verify.png"
			alt="Verify"
			height={500}
		/>
	</ol>
</div>
