<script>
	import ThemesPreview from '../../@components/ThemesPreview.svelte';
</script>

<svelte:head>
	<title>Themes - Hyvor Blogs</title>
</svelte:head>

<div class="wrap">
	<ThemesPreview />
</div>

<style>
	.wrap {
		height: calc(100vh - var(--header-height));
		padding: 15px 20px;
	}
</style>
