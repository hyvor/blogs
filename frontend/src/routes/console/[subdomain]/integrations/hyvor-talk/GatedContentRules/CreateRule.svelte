<script lang="ts">
	import {
		FormControl,
		Modal,
		Radio,
		SplitControl,
		Textarea,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { createEventDispatcher, onMount } from 'svelte';
	import {
		createGatedContentRule,
		getMembershipPlans,
		updateGatedContentRule
	} from '../hyvorTalkActions';
	import TagSelector from './TagSelector.svelte';
	import type { HyvorTalkGatedContentRule, Tag } from '../../../../lib/types';

	export let selectedTags: Tag[] = [];
	export let show = false;
	export let rule: HyvorTalkGatedContentRule | null = null;

	const isUpdate = rule !== null;

	let loading = true;
	let error = '';

	let tag: Tag | null = rule ? rule.tag : null;
	let tagError = '';

	let gateType = rule ? (rule.gate !== null ? 'custom' : 'default') : 'default';
	let gateContent = rule?.gate || '';
	let minimumPlan = rule?.minimum_plan || '';

	let currency: string;
	let plans: { name: string; monthly_price: number }[] = [];

	function prettyCurrency(c: string) {
		c = c.toLowerCase();
		if (c === 'usd') return '$';
		if (c === 'eur') return '€';
		if (c === 'gbp') return '£';
		return c.toUpperCase();
	}

	const dispatch = createEventDispatcher();

	function handleConfirm() {
		tagError = '';

		if (!tag) {
			tagError = 'Please select a tag.';
			return;
		}

		loading = true;

		if (isUpdate) {
			updateGatedContentRule(rule!.id, minimumPlan, gateType === 'default' ? null : gateContent)
				.then((res) => {
					show = false;
					dispatch('update', res);
					toast.success('Rule updated successfully');
				})
				.catch((e) => {
					toast.error(e.message);
				})
				.finally(() => {
					loading = false;
				});
		} else {
			createGatedContentRule(tag.id, minimumPlan, gateType === 'default' ? null : gateContent)
				.then((res) => {
					show = false;
					dispatch('create', res);
					toast.success('Rule created successfully');
				})
				.catch((e) => {
					toast.error(e.message);
				})
				.finally(() => {
					loading = false;
				});
		}
	}

	onMount(() => {
		getMembershipPlans()
			.then((res) => {
				currency = res.currency;
				plans = res.plans;
				if (!isUpdate) minimumPlan = plans[0]?.name || '';
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
	title={isUpdate ? 'Update Gated Content Rule' : 'Create Gated Content Rule'}
	footer={{
		confirm: {
			text: isUpdate ? 'Update Rule' : 'Create Rule'
		}
	}}
	{loading}
	closeOnOutsideClick={false}
	on:confirm={handleConfirm}
>
	<SplitControl label="Tag" caption="Posts with this tag will be gated.">
		<FormControl>
			<TagSelector bind:tag {selectedTags} disabled={isUpdate} />
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
