<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { postVariantLanguageStore } from '../../../postStore';
	import Slug from './Slug.svelte';
	import Description from './Description.svelte';
	import Authors from './Authors/Authors.svelte';
	import Tags from './Tags/Tags.svelte';
	import PublishTime from './PublishTime.svelte';
	import ContentUpdatedAt from './ContentUpdatedAt.svelte';
	import CoverImage from './CoverImage.svelte';
	import Featured from './Featured.svelte';
	import Delete from './Delete.svelte';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import IconCaretRight from '@hyvor/icons/IconCaretRight';

	import CanonicalUrl from './CanonicalUrl.svelte';
	import CodeHead from './CodeHead.svelte';
	import CodeFoot from './CodeFoot.svelte';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	let showAdvanced = $state(false);
</script>

<div class="settings-wrap">
	<Slug />
	<Description />
	<Authors />
	<Tags />
	<CoverImage />
	<PublishTime />
	<ContentUpdatedAt />
	<Featured />
	<Delete />

	{#if $postVariantLanguageStore.is_primary}
		<div class="advanced-wrap">
			<Button color="input" size="small" on:click={() => (showAdvanced = !showAdvanced)}>
				{#snippet end()}
					{@const SvelteComponent = showAdvanced ? IconCaretDown : IconCaretRight}
					<SvelteComponent size={12} />
				{/snippet}
				{i18n.t('console.postEditor.settings.advanced')}
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
