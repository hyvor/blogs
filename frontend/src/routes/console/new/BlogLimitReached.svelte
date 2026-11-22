<script lang="ts">
	import { Button, Callout } from '@hyvor/design/components';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';
	import UsageLimitModal from '../lib/components/UsageLimit/UsageLimitModal.svelte';
	import { orgLimitsStore } from '../lib/stores';

	let show = $state(false);
</script>

<div class="wrap">
	<Callout type="warning">
		{#snippet icon()}
			<IconExclamationCircle />
		{/snippet}
		{#snippet title()}
			<div>Blog limit reached</div>
		{/snippet}
		<div>
			Your organization has reached the maximum number of blogs ({$orgLimitsStore?.blogs}) allowed
			in your plan. Upgrade your plan or add more blogs to create a new blog.
		</div>
		<div class="actions">
			<Button size="small" on:click={() => (show = true)}>Upgrade</Button>
		</div>
	</Callout>
</div>

{#if show}
	<UsageLimitModal bind:show focus="blogs" />
{/if}

<style>
	.wrap {
		margin-bottom: 20px;
	}
	.actions {
		margin-top: 10px;
	}
</style>
