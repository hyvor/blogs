<script lang="ts">
	import logo from '$lib/img/logo.png';
	import { authUserStore } from './lib/stores';
</script>

<div id="bar">
	<div class="inner hds-box">
		<div class="left">
			<a class="logo" href="/">
				<img src={logo} alt="Hyvor Blogs" />
				<span class="name">Hyvor Blogs</span>
			</a>
		</div>
		<div class="right">
			<div class="user">
				<img src={$authUserStore.picture_url} alt={$authUserStore.name} />
			</div>
		</div>
	</div>
</div>

<style>
	#bar {
		padding: 0 12px 10px 15px;
		padding-bottom: 0px;
	}
	.inner {
		padding: 10px 29px;
		display: flex;
		align-items: center;
		border-top-left-radius: 0;
		border-top-right-radius: 0;
	}
	img {
		width: 20px;
		height: 20px;
	}
	.left {
		display: flex;
		align-items: center;
		flex: 1;
	}
	.logo {
		display: flex;
		align-items: center;
		text-decoration: none;
		gap: 5px;
		font-weight: 600;
	}
	.user img {
		border-radius: 50%;
		width: 24px;
		height: 24px;
	}
</style>
