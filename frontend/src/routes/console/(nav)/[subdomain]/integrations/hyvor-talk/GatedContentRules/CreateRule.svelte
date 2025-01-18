<script lang="ts">
	import {
		FormControl,
		Modal,
		Radio,
		SplitControl,
		Textarea,
		Validation,
		toast,
		IconMessage
	} from '@hyvor/design/components';
	import { createEventDispatcher, onMount } from 'svelte';
	import {
		createGatedContentRule,
		getMembershipPlans,
		updateGatedContentRule
	} from '../hyvorTalkActions';
	import TagSelector from './TagSelector.svelte';
	import type { HyvorTalkGatedContentRule, Tag } from '../../../../../lib/types';

	interface Props {
		selectedTags?: Tag[];
		show?: boolean;
		rule?: HyvorTalkGatedContentRule | null;
	}

	let { selectedTags = [], show = $bindable(false), rule = null }: Props = $props();

	const isUpdate = rule !== null;

	let loading = $state(true);
	let error = $state('');

	let tag: Tag | null = $state(rule ? rule.tag : null);
	let tagError = $state('');

	let gateType = $state(rule ? (rule.gate !== null ? 'custom' : 'default') : 'default');
	let gateContent = $state(rule?.gate || '');
	let minimumPlan = $state(rule?.minimum_plan || '');

	let currency: string = $state();
	let plans: { name: string; monthly_price: number }[] = $state([]);

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

				if (plans.length === 0) {
					error =
						'You have not created any membership plans in Hyvor Talk. Please create at least one plan to use this feature.';
					return;
				}

				if (!isUpdate) minimumPlan = plans[0]?.name || '';
			})
			.catch((err) => {
				let msg = err.message;

				if (msg === 'memberships_not_enabled') {
					msg =
						'You have not enabled memberships in Hyvor Talk. Please enable it to use this feature.';
				}

				error = msg;
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
	{#if error}
		<IconMessage error message={error} padding={50} iconSize={60} />
	{:else}
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
	{/if}
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
