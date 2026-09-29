<script>
	import { Table, TableRow, Tooltip } from '@hyvor/design/components';
</script>

# Hyvor Post Integration

Hyvor Blogs natively integrates with [Hyvor Post](https://post.hyvor.com) to provide a newsletter system for your blogs.

- [Features](#features)
- [Pricing](#pricing)
- [Connecting Hyvor Post](#connect)
- [Access Mapping](#access)
- [Self-hosted Deployments](#self-hosted)

<h2 id="features">Features</h2>

- Easily embed the signup form on your blog.
- Access all Hyvor Post features directly from the Hyvor Blogs Console: sending issues, managing subscribers, viewing analytics, etc.
- Automatic access syncing from Hyvor Blogs to Hyvor Post.

<h2 id="pricing">Pricing</h2>

All Hyvor Blogs plans include a <span class="info"><Tooltip text="This complimentary license allows you to use Hyvor Post without additional cost.">complimentary license</Tooltip></span> for Hyvor Post as follows:

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

If you need to send more emails, you have two options: either upgrade your Hyvor Blogs plan or start a separate Hyvor Post subscription. If you start a separate Hyvor Post subscription, your overall email allowance will be the sum of the complimentary allowance (from Hyvor Blogs) and the subscription allowance (from Hyvor Post subscription).

<h2 id="connect">Connecting Hyvor Post</h2>

To connect Hyvor Post to your blog, you have two methods: enable the integration when creating the blog or connect later at **Settings → Integrations → Hyvor Post**.

When you connect Hyvor Post, a new newsletter will be created within your organization. You can manage the newsletter from the Hyvor Blogs Console or directly from the [Hyvor Post Console](https://post.hyvor.com).

<h2 id="access">Access Mapping</h2>

[Users of your blog](/docs/users) will have access to Hyvor Post Console based on their role. Hyvor Blogs will automatically sync the access to the connected newsletter.

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
</Table>

<h2 id="self-hosted">Self-hosted Deployments</h2>

The Hyvor Post integration is only available on Hyvor Blogs Cloud. In self-hosted deployments, you can still add newsletters by pasting an embed code (from Hyvor Post or another newsletter system) directly into **Settings → Comments & Newsletters**.

<style>
	span.info :global(.tooltip-wrap) {
		text-decoration: underline dotted;
	}
</style>
