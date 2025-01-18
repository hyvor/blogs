<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import type { Subscription } from '../../../../lib/types';
	import { IconBoxArrowUpRight } from '@hyvor/icons';

	interface Props {
		subscription: Subscription;
	}

	let { subscription }: Props = $props();
</script>

<div class="current-subscription">
	<div class="plan">
		You are currently on the <strong class="plan-name">{subscription.plan}</strong> plan. Your subscription
		will renew on 2025-01-02. You can manage your billing details (payment method, invoices, etc.) in
		the HYVOR Billing Console.
	</div>
	<div class="button-wrap">
		<Button as="a" href="https://hyvor.com/account/billing" target="_blank" color="blue">
			Manage Billing
			{#snippet end()}
						<IconBoxArrowUpRight  size={14} />
					{/snippet}
		</Button>
	</div>
</div>

<style>
	.current-subscription {
		border-radius: 20px;
	}
	.button-wrap {
		margin-top: 15px;
	}
	.plan-name {
		text-transform: capitalize;
	}
</style>
