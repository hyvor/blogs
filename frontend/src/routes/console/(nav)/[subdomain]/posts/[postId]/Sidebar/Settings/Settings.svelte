<script lang="ts">
	import { Button, SplitControl, TextInput, Textarea } from '@hyvor/design/components';
	import { postVariantLanguageStore, postStore } from '../../../postStore';
	import Slug from './Slug.svelte';
	import Description from './Description.svelte';
	import Authors from './Authors/Authors.svelte';
	import Tags from './Tags/Tags.svelte';
	import PublishTime from './PublishTime.svelte';
	import Status from './Status/Status.svelte';
	import CoverImage from './CoverImage.svelte';
	import Featured from './Featured.svelte';
	import Delete from './Delete.svelte';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import IconCaretRight from '@hyvor/icons/IconCaretRight';

	import CanonicalUrl from './CanonicalUrl.svelte';
	import CodeHead from './CodeHead.svelte';
	import CodeFoot from './CodeFoot.svelte';

	let showAdvanced = $state(false);
</script>

<div class="settings-wrap">
	<Status />
	<Slug />
	<Description />
	<Authors />
	<Tags />
	<CoverImage />
	<PublishTime />
	<Featured />
	<Delete />

	{#if $postVariantLanguageStore.is_primary}
		<div class="advanced-wrap">
			<Button color="input" size="small" on:click={() => (showAdvanced = !showAdvanced)}>
				{#snippet end()}
					{@const SvelteComponent = showAdvanced ? IconCaretDown : IconCaretRight}
					<SvelteComponent size={12} />
				{/snippet}
				Advanced
			</Button>
		</div>
	{/if}

	{#if showAdvanced}
		<CanonicalUrl />
		<CodeHead />
		<CodeFoot />
	{/if}
</div>

<style>
	.settings-wrap :global(.split-control > .left) {
		flex: 3;
		min-width: initial;
	}

	.settings-wrap :global(.split-control > .right) {
		flex: 7;
	}

	.advanced-wrap {
		padding: 10px 10px;
	}
</style>
