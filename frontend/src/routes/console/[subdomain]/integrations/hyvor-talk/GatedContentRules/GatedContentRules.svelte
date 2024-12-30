<script lang="ts">
	import { Button, IconMessage, Label, Loader, SplitControl } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import type { HyvorTalkGatedContentRule } from '../../../../lib/types';
	import CreateRule from './CreateRule.svelte';
	import { getGatedContentRules } from '../hyvorTalkActions';
	import RuleRow from './RuleRow.svelte';

	let loading = true;
	let error: null | string = null;
	let rules: HyvorTalkGatedContentRule[] = [];

	let creating = false;

	function onDelete(e: CustomEvent<number>) {
		const id = e.detail;
		rules = rules.filter((r) => r.id !== id);
	}

	function onCreate(e: CustomEvent<HyvorTalkGatedContentRule>) {
		rules = [...rules, e.detail];
	}

	function onUpdate(e: CustomEvent<HyvorTalkGatedContentRule>) {
		const updatedRule = e.detail;
		rules = rules.map((r) => (r.id === updatedRule.id ? updatedRule : r));
	}

	const MAX_RULES = 10;

	onMount(() => {
		getGatedContentRules()
			.then((res) => {
				rules = res;
			})
			.catch((e) => {
				error = e.message;
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

<SplitControl column>
	<Label slot="label">
		Gated Content Rules <Button
			size="small"
			on:click={() => (creating = true)}
			disabled={rules.length >= MAX_RULES}
		>
			+ Create
		</Button>
	</Label>

	{#if loading}
		<Loader padding={40} />
	{:else if error}
		<IconMessage iconSize={45} error message={error} />
	{:else if rules.length === 0}
		<IconMessage iconSize={60} padding={40} empty message="No gated content rules found." />
	{:else}
		{#each rules as rule}
			<RuleRow {rule} on:delete={onDelete} on:update={onUpdate} />
		{/each}
	{/if}
</SplitControl>

{#if creating}
	<CreateRule
		bind:show={creating}
		selectedTags={rules.map((r) => r.tag).filter((t) => t)}
		on:create={onCreate}
	/>
{/if}
