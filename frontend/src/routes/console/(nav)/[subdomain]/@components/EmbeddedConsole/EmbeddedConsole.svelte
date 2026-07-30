<script lang="ts">
	interface Props {
		url: string;
		title: string;
	}

	let { url, title }: Props = $props();
</script>

<div class="wrap">
	<iframe src={url} frameborder="0" {title}></iframe>
</div>

<style>
	.wrap {
		width: 100%;
		height: 100%;
	}

	iframe {
		width: 100%;
		height: 100%;
		border: none;
	}
</style>
