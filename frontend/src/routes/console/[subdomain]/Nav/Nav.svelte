<script>
	import BottomButtons from './BottomButtons/BottomButtons.svelte';
	import BlogNav from './BlogNav.svelte';
	import AccountNav from './AccountNav.svelte';
</script>

<div id="nav-wrap">
	<div class="nav account">
		<AccountNav />
	</div>
	<div class="nav">
		<BlogNav />
	</div>

	<BottomButtons />
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
