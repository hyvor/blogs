<script lang="ts">
	import { Tag, Tooltip } from '@hyvor/design/components';

	interface Props {
		configured: boolean;
	}

	let { configured }: Props = $props();
</script>

<Tooltip text="Only checks the default configuration. You may have configured it manually.">
	{#if configured}
		<Tag size="small" color="green">Configured</Tag>
	{:else}
		<Tag size="small">Not Configured</Tag>
	{/if}
</Tooltip>
