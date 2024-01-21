<script>
	import ThemesPreview from "../@components/ThemesPreview.svelte";
</script>

<div class="wrap">
    <ThemesPreview />
</div>

<style>
    .wrap {
        height: calc(100vh - var(--header-height));
        padding: 15px;
    }
</style>