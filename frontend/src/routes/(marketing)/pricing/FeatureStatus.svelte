<script lang="ts">
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import IconXCircleFill from '@hyvor/icons/IconXCircleFill';

	interface Props {
		status: boolean | string;
	}

	let { status }: Props = $props();
</script>

<div class="feature-status">
	{#if status === true}
		<IconCheckCircleFill style="color: var(--accent);" />
	{:else if status === false}
		<IconXCircleFill style="color: var(--gray);" />
	{:else}
		{status}
	{/if}
</div>

<style>
	.feature-status {
		padding: 0 15px;
	}
</style>
