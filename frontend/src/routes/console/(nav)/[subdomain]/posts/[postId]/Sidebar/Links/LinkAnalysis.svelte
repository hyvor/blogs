<script lang="ts">
	import { IconMessage, toast } from '@hyvor/design/components';
	import IconEyeSlashFill from '@hyvor/icons/IconEyeSlashFill';
	import IconXCircleFill from '@hyvor/icons/IconXCircleFill';
	import IconExclamationCircleFill from '@hyvor/icons/IconExclamationCircleFill';
	import { Text } from '@hyvor/design/components';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import { Tag } from '@hyvor/design/components';
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import { Loader } from '@hyvor/design/components';
	import { Button } from '@hyvor/design/components';
	import { variantLinksStore, variantLinkCountsStore } from './linksStore';
	import LinkRow from './LinkRow.svelte';
	import { isHttpLink } from '../../../../../../lib/links/links';
	import { callLinkAnalysisApi } from '../../../../tools/link-analysis/linkAnalysisActions';
	import { postVariantStore, updatePostVariantStore } from '../../../postStore';
	import { getResultObjectFromLinks } from './linkLoader';
	import IconSignTurnSlightRight from '@hyvor/icons/IconSignTurnSlightRight';

	let linksCount = $derived($variantLinksStore.length);

	let isReloadingAll = $state(false);

	let linksEl: HTMLDivElement | undefined = $state();

	function handleJump(type: 'ok' | 'broken' | 'redirect' | 'ignored') {
		const el = linksEl?.querySelector('.link-wrap.type-' + type);
		if (el) {
			el.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	}

	function handleReloadAll() {
		isReloadingAll = true;

		const allLinks = $variantLinksStore
			.filter((link) => isHttpLink(link))
			.map((link) => link.originalHref);

		callLinkAnalysisApi($postVariantStore.id, allLinks)
			.then((res) => {
				updatePostVariantStore({
					link_analysis: {
						...$postVariantStore.link_analysis,
						...getResultObjectFromLinks(res)
					}
				});
			})
			.catch(() => {
				toast.error('Failed to recheck links');
			})
			.finally(() => {
				isReloadingAll = false;
			});
	}
</script>

<div class="wrap">
	<div class="top">
		<div class="title">
			Links ({linksCount})
		</div>

		<div class="recheck-wrap">
			{#if linksCount > 0}
				<Button size="small" on:click={handleReloadAll} disabled={isReloadingAll}>
					{#snippet start()}
						<IconArrowClockwise size={14} />
					{/snippet}
					Recheck All
					{#snippet end()}
						<Loader size={12} state={isReloadingAll ? 'loading' : 'none'} />
					{/snippet}
				</Button>
			{/if}
		</div>
	</div>

	<div class="summary">
		{#if $variantLinkCountsStore.ok > 0}
			<Tag size="small" color="green" interactive on:click={() => handleJump('ok')}>
				{#snippet start()}
					<Text bold>{$variantLinkCountsStore.ok}</Text>
				{/snippet}
				OK
				{#snippet end()}
					<IconCheckCircleFill size={12} />
				{/snippet}
			</Tag>
		{/if}

		{#if $variantLinkCountsStore.broken > 0}
			<Tag size="small" color="red" interactive on:click={() => handleJump('broken')}>
				{#snippet start()}
					<Text bold>{$variantLinkCountsStore.broken}</Text>
				{/snippet}
				Broken
				{#snippet end()}
					<IconXCircleFill size={12} />
				{/snippet}
			</Tag>
		{/if}

		{#if $variantLinkCountsStore.risky > 0}
			<Tag size="small" color="orange" interactive on:click={() => handleJump('risky')}>
				{#snippet start()}
					<Text bold>{$variantLinkCountsStore.risky}</Text>
				{/snippet}
				Risky
				{#snippet end()}
					<IconExclamationCircleFill size={12} />
				{/snippet}
			</Tag>
		{/if}

		{#if $variantLinkCountsStore.redirect > 0}
			<Tag size="small" color="blue" interactive on:click={() => handleJump('redirect')}>
				{#snippet start()}
					<Text bold>{$variantLinkCountsStore.redirect}</Text>
				{/snippet}
				Redirect
				{#snippet end()}
					<IconSignTurnSlightRight size={12} />
				{/snippet}
			</Tag>
		{/if}

		{#if $variantLinkCountsStore.ignored > 0}
			<Tag size="small" color="default" interactive on:click={() => handleJump('ignored')}>
				{#snippet start()}
					<Text bold>{$variantLinkCountsStore.ignored}</Text>
				{/snippet}
				Ignored
				{#snippet end()}
					<IconEyeSlashFill size={12} />
				{/snippet}
			</Tag>
		{/if}
	</div>

	<div class="links" bind:this={linksEl}>
		{#if linksCount > 0}
			{#each $variantLinksStore as link}
				<LinkRow {link} />
			{/each}
		{:else}
			<IconMessage empty message="No links found in your post" padding={60} iconSize={60} />
		{/if}
	</div>
</div>

<style lang="scss">
	.wrap {
		display: flex;
		flex-direction: column;
		gap: 10px;
	}
	.top {
		display: flex;
		align-items: center;
		.title {
			font-weight: 600;
			flex: 1;
		}
		.recheck-wrap {
			:global(button) {
				font-size: 12px !important;
			}
		}
	}
	.links {
		flex: 1;
		overflow: auto;
	}
</style>
