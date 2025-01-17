<script lang="ts">
	import { IconButton, Tag, confirm, toast } from '@hyvor/design/components';
	import type { HyvorTalkGatedContentRule } from '../../../../../lib/types';
	import TagName from '../../../settings/tags/TagName.svelte';
	import { IconPencil, IconTrash } from '@hyvor/icons';
	import { deleteGatedContentRule } from '../hyvorTalkActions';
	import { createEventDispatcher } from 'svelte';
	import CreateRule from './CreateRule.svelte';

	export let rule: HyvorTalkGatedContentRule;

	let updating = false;

	const dispatch = createEventDispatcher<{
		delete: number;
	}>();

	function getGateText(gate: string | null) {
		if (gate === null) {
			return 'Default';
		}

		function gateSubstr(gate: string) {
			return gate.length > 10 ? gate.substring(0, 10) + '...' : gate;
		}

		return 'Custom (' + gateSubstr(gate) + ')';
	}

	async function onDelete() {
		const confirmed = await confirm({
			title: 'Delete Gated Content Rule',
			content: 'Are you sure you want to delete this rule?',
			confirmText: 'Delete',
			autoClose: false,
			danger: true
		});

		if (confirmed) {
			confirmed.loading();

			deleteGatedContentRule(rule.id)
				.then(() => {
					toast.success('Rule deleted successfully');
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
		<div class="bottom">Tag</div>
	</div>
	<div class="min-plan">
		<div>
			{rule.minimum_plan}
		</div>
		<div class="bottom">Minimum Plan</div>
	</div>
	<div class="gate">
		<div>
			{getGateText(rule.gate)}
		</div>
		<div class="bottom">Gate</div>
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
