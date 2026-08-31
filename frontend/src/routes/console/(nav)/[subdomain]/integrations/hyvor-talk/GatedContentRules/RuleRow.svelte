<script lang="ts">
	import { IconButton, Tag, confirm, toast } from '@hyvor/design/components';
	import type { HyvorTalkGatedContentRule } from '../../../../../lib/types';
	import TagName from '../../../settings/tags/TagName.svelte';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { deleteGatedContentRule } from '../hyvorTalkActions';
	import { createEventDispatcher } from 'svelte';
	import CreateRule from './CreateRule.svelte';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		rule: HyvorTalkGatedContentRule;
	}

	let { rule }: Props = $props();

	let updating = $state(false);

	const dispatch = createEventDispatcher<{
		delete: number;
	}>();

	function getGateText(gate: string | null) {
		if (gate === null) {
			return i18n.t('console.integrations.hyvorTalk.gatedContent.gateDefault');
		}

		function gateSubstr(gate: string) {
			return gate.length > 10 ? gate.substring(0, 10) + '...' : gate;
		}

		return i18n.t('console.integrations.hyvorTalk.gatedContent.gateCustom', {
			gate: gateSubstr(gate)
		});
	}

	async function onDelete() {
		const confirmed = await confirm({
			title: i18n.t('console.integrations.hyvorTalk.gatedContent.deleteTitle'),
			content: i18n.t('console.integrations.hyvorTalk.gatedContent.deleteContent'),
			confirmText: i18n.t('console.common.delete'),
			autoClose: false,
			danger: true
		});

		if (confirmed) {
			confirmed.loading();

			deleteGatedContentRule(rule.id)
				.then(() => {
					toast.success(i18n.t('console.integrations.hyvorTalk.gatedContent.ruleDeleted'));
					dispatch('delete', rule.id);
				})
				.catch((e) => {
					toast.error(e.message);
				})
				.finally(() => {
					confirmed.close();
				});
		}
	}
</script>

<div class="row">
	<div class="tag">
		{#if rule.tag}
			<Tag size="small">
				<TagName tag={rule.tag} />
			</Tag>
		{/if}
		<div class="bottom">{i18n.t('console.integrations.hyvorTalk.gatedContent.tag')}</div>
	</div>
	<div class="min-plan">
		<div>
			{rule.minimum_plan}
		</div>
		<div class="bottom">{i18n.t('console.integrations.hyvorTalk.gatedContent.minimumPlan')}</div>
	</div>
	<div class="gate">
		<div>
			{getGateText(rule.gate)}
		</div>
		<div class="bottom">{i18n.t('console.integrations.hyvorTalk.gatedContent.gate')}</div>
	</div>
	<div class="actions">
		<IconButton color="input" size="small" on:click={() => (updating = true)}>
			<IconPencil size={12} />
		</IconButton>
		<IconButton color="input" size="small" on:click={onDelete}>
			<IconTrash size={12} />
		</IconButton>
	</div>
</div>

{#if updating}
	<CreateRule bind:show={updating} {rule} on:update />
{/if}

<style>
	.row {
		display: flex;
		align-items: center;
		padding: 15px 20px;
		margin: 5px 0;
		border: 1px solid var(--border);
		border-radius: 20px;
	}
	.tag,
	.min-plan,
	.gate {
		flex: 1;
	}
	.bottom {
		font-size: 14px;
		color: var(--text-light);
	}
</style>
