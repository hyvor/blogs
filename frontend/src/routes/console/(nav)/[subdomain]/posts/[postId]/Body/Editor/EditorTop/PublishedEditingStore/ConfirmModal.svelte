<script lang="ts">
	interface Props {
		words?: number;
	}

	let { words = 0 }: Props = $props();
</script>

Are you sure you want to discard the unpublished changes.

<ul>
	<li>
		You have about <strong>{words} changed words</strong>.
	</li>
	<li>This action cannot be undone.</li>
	<li>The content will be reverted to the last published version.</li>
</ul>
