<script lang="ts">
	import {
		FormControl,
		Modal,
		Radio,
		SplitControl,
		Textarea,
		Validation
	} from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { createGatedContentRule, getMembershipPlans } from '../hyvorTalkActions';
	import TagSelector from './TagSelector.svelte';
	import type { Tag } from '../../../../lib/types';

	export let show = false;

	let loading = true;
	let error = '';

	let tag: Tag;
	let tagError = '';

	let gateType = 'default';
	let gateContent = '';
	let minimumPlan: string;

	let currency: string;
	let plans: { name: string; monthly_price: number }[] = [];

	function prettyCurrency(c: string) {
		c = c.toLowerCase();
		if (c === 'usd') return '$';
		if (c === 'eur') return '€';
		if (c === 'gbp') return '£';
		return c.toUpperCase();
	}

	function handleConfirm() {
		tagError = '';

		if (!tag) {
			tagError = 'Please select a tag.';
			return;
		}

		loading = true;

		createGatedContentRule(tag.id, minimumPlan, gateType === 'default' ? null : gateContent)
			.then(() => {
				show = false;
			})
			.finally(() => {
				loading = false;
			});
	}

	onMount(() => {
		getMembershipPlans()
			.then((res) => {
				currency = res.currency;
				plans = res.plans;
				minimumPlan = plans[0]?.name || '';
			})
			.catch((err) => {
				error = err.message;
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

<Modal
	bind:show
	title="Create Gated Content Rule"
	footer={{
		confirm: {
			text: 'Create Rule'
		}
	}}
	{loading}
	closeOnOutsideClick={false}
	on:confirm={handleConfirm}
>
	<SplitControl label="Tag" caption="Posts with this tag will be gated.">
		<FormControl>
			<TagSelector bind:tag />
			{#if tagError}
				<Validation type="error">{tagError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>
	<SplitControl
		label="Minimum Plan"
		caption="Users with a plan lower than this will not be able to view the content."
	>
		<FormControl>
			{#each plans as plan}
				<Radio bind:group={minimumPlan} value={plan.name} name="minimumPlan">
					{plan.name}
					<span class="price">({prettyCurrency(currency)}{plan.monthly_price}/month)</span>
				</Radio>
			{/each}
		</FormControl>
	</SplitControl>
	<SplitControl
		label="Gate"
		caption="The content to show when the user is not allowed to view the content."
	>
		<FormControl>
			<Radio bind:group={gateType} value="default" name="gate">Default Gate</Radio>
			<Radio bind:group={gateType} value="custom" name="gate">Custom Gate</Radio>

			{#if gateType === 'custom'}
				<Textarea bind:value={gateContent} placeholder="Custom gate name or HTML" />
			{/if}
		</FormControl>
	</SplitControl>
</Modal>

<style>
	.price {
		color: var(--text-light);
		font-size: 14px;
		margin-left: 6px;
		vertical-align: middle;
		font-weight: normal;
	}
</style>
