<script lang="ts">
	interface Props {
		content: string;
	}

	let { content }: Props = $props();
</script>

<div class="user-message">
	<div class="message">{content}</div>
</div>

<style>
	.user-message {
		padding: 35px 30px 20px 30px;
		display: flex;
		justify-content: flex-end;
	}

	.message {
		background-color: color-mix(in srgb, var(--accent), transparent 85%);
		padding: 8px 18px;
		border-radius: 20px;
		max-width: 500px;
	}
</style>
