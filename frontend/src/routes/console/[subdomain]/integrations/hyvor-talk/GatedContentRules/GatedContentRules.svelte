<script lang="ts">
	import { Button, IconMessage, Label, Loader, SplitControl } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { getGatedContentRules } from './gatedContentRulesActions';
	import type { HyvorTalkGatedContentRule } from '../../../../lib/types';

	let loading = true;
	let error: null | string = null;
	let rules: HyvorTalkGatedContentRule[] = [];

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
		Gated Content Rules <Button size="small">+ Create</Button>
	</Label>

	{#if loading}
		<Loader padding={40} />
	{:else if error}
		<IconMessage iconSize={45} error message={error} />
	{:else if rules.length === 0}
		<IconMessage iconSize={60} padding={40} empty message="No gated content rules found." />
	{:else}
		{#each rules as rule}
			<div>{rule.id}</div>
		{/each}
	{/if}
</SplitControl>
