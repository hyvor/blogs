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
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

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

	let currency: string = $state('');
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
			tagError = i18n.t('console.integrations.hyvorTalk.gatedContent.selectTagError');
			return;
		}

		loading = true;

		if (isUpdate) {
			updateGatedContentRule(rule!.id, minimumPlan, gateType === 'default' ? null : gateContent)
				.then((res) => {
					show = false;
					dispatch('update', res);
					toast.success(i18n.t('console.integrations.hyvorTalk.gatedContent.ruleUpdated'));
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
					toast.success(i18n.t('console.integrations.hyvorTalk.gatedContent.ruleCreated'));
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
					error = i18n.t('console.integrations.hyvorTalk.gatedContent.noPlans');
					return;
				}

				if (!isUpdate) minimumPlan = plans[0]?.name || '';
			})
			.catch((err) => {
				let msg = err.message;

				if (msg === 'memberships_not_enabled') {
					msg = i18n.t('console.integrations.hyvorTalk.gatedContent.membershipsNotEnabled');
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
	title={isUpdate
		? i18n.t('console.integrations.hyvorTalk.gatedContent.updateRuleTitle')
		: i18n.t('console.integrations.hyvorTalk.gatedContent.createRuleTitle')}
	footer={{
		confirm: {
			text: isUpdate
				? i18n.t('console.integrations.hyvorTalk.gatedContent.updateRule')
				: i18n.t('console.integrations.hyvorTalk.gatedContent.createRule')
		}
	}}
	{loading}
	closeOnOutsideClick={false}
	on:confirm={handleConfirm}
>
	{#if error}
		<IconMessage error message={error} padding={50} iconSize={60} />
	{:else}
		<SplitControl
			label={i18n.t('console.integrations.hyvorTalk.gatedContent.tag')}
			caption={i18n.t('console.integrations.hyvorTalk.gatedContent.tagCaption')}
		>
			<FormControl>
				<TagSelector bind:tag {selectedTags} disabled={isUpdate} />
				{#if tagError}
					<Validation type="error">{tagError}</Validation>
				{/if}
			</FormControl>
		</SplitControl>
		<SplitControl
			label={i18n.t('console.integrations.hyvorTalk.gatedContent.minimumPlan')}
			caption={i18n.t('console.integrations.hyvorTalk.gatedContent.minimumPlanCaption')}
		>
			<FormControl>
				{#each plans as plan}
					<Radio bind:group={minimumPlan} value={plan.name} name="minimumPlan">
						{plan.name}
						<span class="price">
							{i18n.t('console.integrations.hyvorTalk.gatedContent.perMonth', {
								price: prettyCurrency(currency) + plan.monthly_price
							})}
						</span>
					</Radio>
				{/each}
			</FormControl>
		</SplitControl>
		<SplitControl
			label={i18n.t('console.integrations.hyvorTalk.gatedContent.gate')}
			caption={i18n.t('console.integrations.hyvorTalk.gatedContent.gateCaption')}
		>
			<FormControl>
				<Radio bind:group={gateType} value="default" name="gate">
					{i18n.t('console.integrations.hyvorTalk.gatedContent.defaultGate')}
				</Radio>
				<Radio bind:group={gateType} value="custom" name="gate">
					{i18n.t('console.integrations.hyvorTalk.gatedContent.customGate')}
				</Radio>

				{#if gateType === 'custom'}
					<Textarea
						bind:value={gateContent}
						placeholder={i18n.t('console.integrations.hyvorTalk.gatedContent.customGatePlaceholder')}
					/>
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
