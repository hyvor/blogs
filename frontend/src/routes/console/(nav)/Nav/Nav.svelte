<script>
	import BlogNav from './BlogNav.svelte';
	import AccountNav from './AccountNav.svelte';
	import { blogStore } from '../../lib/stores/blogStore';
	import { blogListStore } from '../../lib/stores';
	import { getConfig } from '../../lib/config';
	import { LanguageToggle } from '@hyvor/design/components';

	let blogListItemId = $derived($blogStore ? $blogStore.id : $blogListStore[0]?.id);
	let blogListItem = $derived($blogListStore.find((blog) => blog.id === blogListItemId));
</script>

<div id="nav-wrap">
	{#if getConfig().deployment === 'cloud'}
		<div class="nav account">
			<AccountNav />
		</div>
	{/if}

	{#if blogListItem}
		<div class="nav">
			<BlogNav listItem={blogListItem} />
		</div>
	{/if}

	<div class="bottom">
		<div class="nav lang">
			<LanguageToggle position="top" />
		</div>
	</div>
</div>

<style>
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

	.bottom {
		flex: 1;
		display: flex;
		flex-direction: column;
		justify-content: flex-end;
		margin-top: 15px;
	}

	.nav.lang {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 10px 15px;
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

		.bottom {
			display: none;
		}
	}
</style>
