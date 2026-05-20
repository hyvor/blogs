<script lang="ts">
	import { NavLink } from '@hyvor/design/components';
	import IconBarChart from '@hyvor/icons/IconBarChart';
	import IconCardChecklist from '@hyvor/icons/IconCardChecklist';
	import { page } from '$app/stores';
</script>

<div class="nav">
	<div class="nav-links hds-box">
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

	.nav-links :global(a.active) {
		background-color: var(--accent-light-mid);
	}
</style>
