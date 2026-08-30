<script lang="ts">
	import { Table, TableRow, Tooltip } from '@hyvor/design/components';
</script>

# Hyvor Talk Integration

Hyvor Blogs natively integrates with [Hyvor Talk](https://talk.hyvor.com), a privacy-first commenting platform, to add comments to your blog posts.

- [Features](#features)
- [Pricing](#pricing)
- [Connecting Hyvor Talk](#connect)
- [Embed Code](#embed-code)
- [Access Mapping](#access)
- [Self-hosted Deployments](#self-hosted)

<h2 id="features">Features</h2>

- Adds a privacy-first, ad-free commenting widget to your posts automatically.
- Manage comments, moderators, and settings directly from the Hyvor Blogs Console or the Hyvor Talk Console.
- Automatic access syncing from Hyvor Blogs to Hyvor Talk.

<h2 id="pricing">Pricing</h2>

All Hyvor Blogs plans include a <span class="info"><Tooltip text="This complimentary license allows you to use Hyvor Talk without additional cost.">complimentary license</Tooltip></span> for Hyvor Talk as follows:

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

If you need more credits, you have two options: either upgrade your Hyvor Blogs plan or start a separate Hyvor Talk subscription. If you start a separate Hyvor Talk subscription, your overall credit allowance will be the sum of the complimentary allowance (from Hyvor Blogs) and the subscription allowance (from Hyvor Talk subscription).

<h2 id="connect">Connecting Hyvor Talk</h2>

To connect Hyvor Talk to your blog, go to **Settings &rarr; Integrations &rarr; Hyvor Talk** and click **Connect Now**.

When you connect, a new website is created within your organization on Hyvor Talk, and your blog is linked to it. You can manage comments from the Hyvor Blogs Console or directly from the [Hyvor Talk Console](https://talk.hyvor.com).

Disconnecting removes the website from Hyvor Talk entirely, along with all its comments, moderators, and settings, so make sure this is what you want before disconnecting.

<h2 id="embed-code">Embed Code</h2>

Once connected, Hyvor Blogs automatically adds the Hyvor Talk embed code to your blog's [`_comments` variable](/docs/themes-templates#placeholders), which themes typically render below the post content.

You can customize the embed code at **Settings &rarr; Integrations &rarr; Hyvor Talk** - for example, to change comment sorting or other [embed options](https://talk.hyvor.com/docs/embed). You can reset it back to the default at any time.

<h2 id="access">Access Mapping</h2>

[Users of your blog](/docs/users) will have access to the Hyvor Talk Console based on their role. Hyvor Blogs will automatically sync this access to the connected website as users are added, removed, or change roles.

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

The Hyvor Talk integration is only available on Hyvor Blogs Cloud. In self-hosted deployments, you can still add comments by pasting an embed code (from Hyvor Talk or another commenting system) directly into **Settings &rarr; Comments & Newsletters**.

<style>
	span.info :global(.tooltip-wrap) {
		text-decoration: underline dotted;
	}
</style>
