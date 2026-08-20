<script>
	import { Table, TableRow, Tooltip } from '@hyvor/design/components';
</script>

<h1>Hyvor Post Integration</h1>

<p>
	Hyvor Blogs natively integrates with <a href="https://post.hyvor.com">Hyvor Post</a> to provide a newsletter
	system for your blogs.
</p>

<ul>
	<li>
		<a href="#features">Features</a>
	</li>
	<li>
		<a href="#pricing">Pricing</a>
	</li>
	<li>
		<a href="#connect">Connecting Hyvor Post</a>
	</li>
	<li>
		<a href="#access">Access Mapping</a>
	</li>
</ul>

<h2 id="features">Features</h2>

<ul>
	<li>Easily embed the signup form on your blog.</li>
	<li>
		Access all Hyvor Post features directly from the Hyvor Blogs Console: sending issues, managing
		subscribers, viewing analytics, etc.
	</li>
	<li>Automatic access syncing from Hyvor Blogs to Hyvor Post.</li>
</ul>

<h2 id="pricing">Pricing</h2>

<p>
	All Hyvor Blogs plans include a <span class="info"
		><Tooltip
			text="This complimentary license allows you to use Hyvor Post without additional cost."
			>complimentary license</Tooltip
		></span
	> for Hyvor Post as follows:
</p>

<Table columns="1fr 1fr" style="bordered">
	<TableRow head>
		<div>Hyvor Blogs Plan</div>
		<div>Hyvor Post Email Allowance</div>
	</TableRow>
	<TableRow>
		<div>Trial</div>
		<div><i>test emails only</i></div>
	</TableRow>
	<TableRow>
		<div>Personal</div>
		<div>5k emails/month</div>
	</TableRow>
	<TableRow>
		<div>Starter</div>
		<div>15k emails/month</div>
	</TableRow>
	<TableRow>
		<div>Growth</div>
		<div>50k emails/month</div>
	</TableRow>
	<TableRow>
		<div>Premium</div>
		<div>150k emails/month</div>
	</TableRow>
</Table>

<p>
	If you need to send more emails, you have two options: either upgrade your Hyvor Blogs plan or
	start a separate Hyvor Post subscription. If you start a separate Hyvor Post subscription, your
	overall email allowance will be the sum of the complimentary allowance (from Hyvor Blogs) and the
	subscription allowance (from Hyvor Post subscription).
</p>

<h2 id="connect">Connecting Hyvor Post</h2>

<p>
	To connect Hyvor Post to your blog, you have two methods: enable the integration when creating the
	blog or connect later at <strong>Settings &rarr; Integrations &rarr; Hyvor Post</strong>.
</p>

<p>
	When you connect Hyvor Post, a new newsletter will be created within your organization. You can
	manage the newsletter from the Hyvor Blogs Console or directly from the <a
		href="https://post.hyvor.com">Hyvor Post Console</a
	>.
</p>

<h2 id="access">Access Mapping</h2>

<p>
	<a href="/docs/users">Users of your blog</a> will have access to Hyvor Post Console based on their role.
	Hyvor Blogs will automatically sync the access to the connected newsletter.
</p>

<Table columns="1fr 1fr" style="bordered">
	<TableRow head>
		<div>Hyvor Blogs Role</div>
		<div>Hyvor Post Role</div>
	</TableRow>
	<TableRow>
		<div>Blog Admin</div>
		<div>User</div>
	</TableRow>
	<TableRow>
		<div>Editor</div>
		<div>User</div>
	</TableRow>
	<TableRow>
		<div>Author</div>
		<div><i>None</i></div>
	</TableRow>
	<TableRow>
		<div>Contributor</div>
		<div><i>None</i></div>
	</TableRow>
	<TableRow>
		<div>Subscriber</div>
		<div><i>None</i></div>
	</TableRow>
</Table>

<style>
	span.info :global(.tooltip-wrap) {
		text-decoration: underline dotted;
	}
</style>
