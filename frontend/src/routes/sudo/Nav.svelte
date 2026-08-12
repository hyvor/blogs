<script lang="ts">
	import { Divider, NavLink, NavLinkGroup } from '@hyvor/design/components';
	import IconBarChart from '@hyvor/icons/IconBarChart';
	import IconCardChecklist from '@hyvor/icons/IconCardChecklist';
	import { page } from '$app/stores';
	import IconSend from '@hyvor/icons/IconSend';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
</script>

<div class="nav">
	<div class="nav-links hds-box">
		<NavLinkGroup activeBackground="var(--accent-light-mid)">
			<NavLink href="/sudo" active={$page.url.pathname === '/sudo'}>
				{#snippet start()}
					<IconBarChart />
				{/snippet}
				Overview
			</NavLink>
			<NavLink href="/sudo/blogs" active={$page.url.pathname.startsWith('/sudo/blogs')}>
				{#snippet start()}
					<IconCardChecklist />
				{/snippet}
				Blogs
			</NavLink>
			<Divider margin={15} />
			<NavLink href="/api/sudo/messenger" target="_blank">
				{#snippet start()}
					<IconSend />
				{/snippet}
				Messenger
				{#snippet end()}
					<IconBoxArrowUpRight size={14} />
				{/snippet}
			</NavLink>
		</NavLinkGroup>
	</div>
</div>

<style>
	.nav {
		width: 280px;
		padding: 15px;
		padding-right: 0;
		flex-shrink: 0;
	}

	.nav-links {
		height: calc(100vh - 30px);
		padding: 15px 0;
		overflow: hidden;
	}
</style>
