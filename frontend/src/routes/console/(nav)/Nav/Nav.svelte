<script>
	import BlogNav from './BlogNav.svelte';
	import AccountNav from './AccountNav.svelte';
	import { blogStore } from '../../lib/stores/blogStore';
	import { blogListStore } from '../../lib/stores';
	import { isTempStore } from '../../lib/temp';

	let blogListItemId = $derived($blogStore ? $blogStore.id : $blogListStore[0]?.id);
	let blogListItem = $derived($blogListStore.find((blog) => blog.id === blogListItemId));
</script>

<div id="nav-wrap">
	{#if !$isTempStore}
		<div class="nav account">
			<AccountNav />
		</div>
	{/if}

	{#if blogListItem}
		<div class="nav">
			<BlogNav listItem={blogListItem} />
		</div>
	{/if}
</div>

<style lang="scss">
	#nav-wrap {
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.nav {
		border-radius: var(--box-radius);
		background: var(--box-background);
		box-shadow: var(--box-shadow);
	}

	.nav.account {
		margin-bottom: 15px;
	}

	@media (max-width: 992px) {
		#nav-wrap {
			flex-direction: row;
			height: initial;
			flex: 1;
		}

		.nav {
			border-radius: 0;
			box-shadow: none;
			border-bottom: none;
			width: 100%;
		}
	}
</style>
