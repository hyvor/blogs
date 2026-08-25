<script>
	import { Table, TableRow, Tooltip } from '@hyvor/design/components';
</script>

<h1>Hyvor Talk Integration</h1>

<p>
	Hyvor Blogs natively integrates with <a href="https://talk.hyvor.com">Hyvor Talk</a>, a
	privacy-first commenting platform, to add comments to your blog posts.
</p>

<ul>
	<li>
		<a href="#features">Features</a>
	</li>
	<li>
		<a href="#pricing">Pricing</a>
	</li>
	<li>
		<a href="#connect">Connecting Hyvor Talk</a>
	</li>
	<li>
		<a href="#embed-code">Embed Code</a>
	</li>
	<li>
		<a href="#access">Access Mapping</a>
	</li>
	<li>
		<a href="#self-hosted">Self-hosted Deployments</a>
	</li>
</ul>

<h2 id="features">Features</h2>

<ul>
	<li>Adds a privacy-first, ad-free commenting widget to your posts automatically.</li>
	<li>
		Manage comments, moderators, and settings directly from the Hyvor Blogs Console or the Hyvor
		Talk Console.
	</li>
	<li>Automatic access syncing from Hyvor Blogs to Hyvor Talk.</li>
</ul>

<h2 id="pricing">Pricing</h2>

<p>
	All Hyvor Blogs plans include a <span class="info"
		><Tooltip
			text="This complimentary license allows you to use Hyvor Talk without additional cost."
			>complimentary license</Tooltip
		></span
	> for Hyvor Talk as follows:
</p>

<Table columns="1fr 1fr" style="bordered">
	<TableRow head>
		<div>Hyvor Blogs Plan</div>
		<div>Hyvor Talk Credits</div>
	</TableRow>
	<TableRow>
		<div>Trial</div>
		<div>1k credits/month</div>
	</TableRow>
	<TableRow>
		<div>Personal</div>
		<div>10k credits/month</div>
	</TableRow>
	<TableRow>
		<div>Starter</div>
		<div>25k credits/month</div>
	</TableRow>
	<TableRow>
		<div>Growth</div>
		<div>100k credits/month</div>
	</TableRow>
	<TableRow>
		<div>Premium</div>
		<div>250k credits/month</div>
	</TableRow>
</Table>

<p>
	If you need more credits, you have two options: either upgrade your Hyvor Blogs plan or start a
	separate Hyvor Talk subscription. If you start a separate Hyvor Talk subscription, your overall
	credit allowance will be the sum of the complimentary allowance (from Hyvor Blogs) and the
	subscription allowance (from Hyvor Talk subscription).
</p>

<h2 id="connect">Connecting Hyvor Talk</h2>

<p>
	To connect Hyvor Talk to your blog, go to <strong
		>Settings &rarr; Integrations &rarr; Hyvor Talk</strong
	>
	and click <strong>Connect Now</strong>.
</p>

<p>
	When you connect, a new website is created within your organization on Hyvor Talk, and your blog
	is linked to it. You can manage comments from the Hyvor Blogs Console or directly from the <a
		href="https://talk.hyvor.com">Hyvor Talk Console</a
	>.
</p>

<p>
	Disconnecting removes the website from Hyvor Talk entirely, along with all its comments,
	moderators, and settings, so make sure this is what you want before disconnecting.
</p>

<h2 id="embed-code">Embed Code</h2>

<p>
	Once connected, Hyvor Blogs automatically adds the Hyvor Talk embed code to your blog's <a
		href="/docs/themes-templates#placeholders"><code>_comments</code> variable</a
	>, which themes typically render below the post content.
</p>

<p>
	You can customize the embed code at <strong>Settings &rarr; Integrations &rarr; Hyvor Talk</strong
	>
	- for example, to change comment sorting or other
	<a href="https://talk.hyvor.com/docs/embed">embed options</a>. You can reset it back to the
	default at any time.
</p>

<h2 id="access">Access Mapping</h2>

<p>
	<a href="/docs/users">Users of your blog</a> will have access to the Hyvor Talk Console based on their
	role. Hyvor Blogs will automatically sync this access to the connected website as users are added, removed,
	or change roles.
</p>

<Table columns="1fr 1fr" style="bordered">
	<TableRow head>
		<div>Hyvor Blogs Role</div>
		<div>Hyvor Talk Role</div>
	</TableRow>
	<TableRow>
		<div>Blog Admin</div>
		<div>Admin</div>
	</TableRow>
	<TableRow>
		<div>Editor</div>
		<div>Moderator</div>
	</TableRow>
	<TableRow>
		<div>Writer</div>
		<div><i>None</i></div>
	</TableRow>
	<TableRow>
		<div>Contributor</div>
		<div><i>None</i></div>
	</TableRow>
</Table>

<h2 id="self-hosted">Self-hosted Deployments</h2>

<p>
	The Hyvor Talk integration is only available on Hyvor Blogs Cloud. In self-hosted deployments, you
	can still add comments by pasting an embed code (from Hyvor Talk or another commenting system)
	directly into <strong>Settings &rarr; Comments & Newsletters</strong>.
</p>

<style>
	span.info :global(.tooltip-wrap) {
		text-decoration: underline dotted;
	}
</style>
