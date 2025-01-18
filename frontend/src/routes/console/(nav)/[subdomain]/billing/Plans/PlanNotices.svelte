<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import { isInTrial, subscriptionStore } from '../../../../lib/stores/subscriptionStore';
	import { IconClock, IconExclamationCircle } from '@hyvor/icons';

	const trialDays = 5 as number;
</script>

<div class="wrap">
	<!-- In Trial -->
	{#if !$subscriptionStore && isInTrial()}
		<Callout type="info">
			<div slot="title">Trial in Progress</div>
			<IconClock slot="icon" />
			Trial ends in <b>{trialDays} day{trialDays !== 1 ? 's' : ''}</b>. Upgrade now to continue
			using your blog.
		</Callout>
	{/if}

	<!-- Trial Ended -->
	{#if !$subscriptionStore && !isInTrial()}
		<Callout type="warning">
			<div slot="title">Trial Ended</div>
			<IconExclamationCircle slot="icon" />
			Your trial has ended. Upgrade now to continue using your blog.
		</Callout>
	{/if}
</div>

<style>
	.wrap:not(:empty) {
		margin-bottom: 15px;
	}
</style>
