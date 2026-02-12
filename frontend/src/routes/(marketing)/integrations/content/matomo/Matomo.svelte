<script lang="ts">
	import { Callout, CodeBlock } from '@hyvor/design/components';
	import { Docs, DocsImage } from '@hyvor/design/marketing';
	import createAccountImage from './create-account.png';
	import copyCodeImage from './copy-code.png';
	import pasteCodeImage from './paste-code.png';
	import clickjsImage from './click-js.png';
</script>

<div class="guide-content">
	<Callout type="info">
		<p><b>Requirements</b>:</p>
		<ul>
			<li>a blog created using <a href="/console">Hyvor Blogs</a></li>
		</ul>
	</Callout>
	<h2>Step 1: Create a Matomo Account</h2>
	<p>
		Go to <a href="https://matomo.org/start-free-analytics-trial/" rel="noopener" target="_blank"
			>Matomo Analytics</a
		>' signup page and enter your email and blog's address (URL) to get started.
	</p>
	<DocsImage src={createAccountImage} alt="Create Matomo Account" />

	<h2>Step 2: Copy the Javascript integration code</h2>
	<ul>
		<li>
			Choose "<b>Install with Javascript Code</b>".
		</li>
		<DocsImage src={clickjsImage} alt="Click on Javascript" />
		<li>Copy the tracking code.</li>
		<DocsImage src={copyCodeImage} alt="Copy Code" />
	</ul>

	<h2>Step 3: Paste the code in Hyvor Blogs console</h2>
	<ul>
		<li>
			Go to <b><a href="/console">Hyvor Blogs Console</a> -> Settings -> Custom Code</b>.
		</li>
		<li>
			Paste the copied code in the <b>Head Code</b> section.
		</li>
		<p>
			It is recommended by Matomo to paste this code before the <code>&lt;/head&gt;</code> tag.
		</p>
		<DocsImage src={pasteCodeImage} alt="Paste Code" />
		<li>Click "Save" to save the changes.</li>
	</ul>
	<p>
		This is will be applied to your entire blog since Matomo needs to track analytics of your entire
		blog.
	</p>

	<p>
		Now your blog is fully-integrated with Matomo Analytics. For more information and advanced
		configurations, use the official <a
			href="https://developer.matomo.org/guides/tracking-javascript-guide"
			rel="noopener"
			target="_blank">Matomo Javascript Tracking documentation</a
		>.
	</p>
</div>
