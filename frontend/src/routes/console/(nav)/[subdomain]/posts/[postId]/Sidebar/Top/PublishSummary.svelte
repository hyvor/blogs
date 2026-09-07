<script lang="ts">
	import { onMount } from 'svelte';
	import { Button, Tag, Text, Validation } from '@hyvor/design/components';
	import IconGear from '@hyvor/icons/IconGear';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';
	import IconExclamationTriangle from '@hyvor/icons/IconExclamationTriangle';
	import IconArrowRight from '@hyvor/icons/IconArrowRight';
	import IconPlus from '@hyvor/icons/IconPlus';
	import IconDash from '@hyvor/icons/IconDash';
	import dayjs from 'dayjs';
	import {
		documentStore,
		postOriginalStore,
		postStore,
		postVariantOriginalStore,
		postVariantStore
	} from '../../../postStore';
	import AuthorTag from '../../../AuthorTag.svelte';
	import TagChip from '../../../TagChip.svelte';
	import Diff from '$lib/components/Diff/Diff.svelte';
	import Compare from '../../Body/Compare/Compare.svelte';
	import ContentChange from './PublishedUpdate/Changes/ContentChange.svelte';
	import { getPublishedChanges } from './PublishedUpdate/published-changes';
	import { getPrimaryLanguage } from '../../../../../../lib/stores/languagesStore';
	import { emptyPublishIssues, getPublishIssues, type PublishIssues } from './publishIssues';
	import { emptyContentStats, getContentStats, type ContentStats } from './contentStats';
	import { getI18n } from '../../../../../../lib/i18n';
	import type { Tag as TagType, User } from '../../../../../../lib/types';

	const i18n = getI18n();

	interface Props {
		onEditSettings?: () => void;
		onIssues?: (issues: PublishIssues) => void;
		diff?: boolean;
	}

	let { onEditSettings, onIssues, diff = false }: Props = $props();

	let issues: PublishIssues = $state(emptyPublishIssues);
	let stats: ContentStats = $state(emptyContentStats);

	const primaryLanguage = getPrimaryLanguage();

	onMount(() => {
		issues = getPublishIssues();
		onIssues?.(issues);

		stats = getContentStats();
	});

	let changes = $state<ReturnType<typeof getPublishedChanges> | null>(null);
	let compareOpen = $state(false);

	$effect(() => {
		if (!diff) return;
		// re-derive whenever the post / variant / document changes
		void $postStore;
		void $postOriginalStore;
		void $postVariantStore;
		void $postVariantOriginalStore;
		void $documentStore;
		changes = getPublishedChanges();
	});

	let errorCount = $derived((issues.titleError ? 1 : 0) + (issues.suggestionsError ? 1 : 0));
	let warningCount = $derived((issues.slugWarning ? 1 : 0) + (issues.descriptionWarning ? 1 : 0));

	let contentChanged = $derived(!!changes && changes.variant.content !== undefined);
	let titleChanged = $derived(!!changes && changes.variant.title !== undefined);
	let slugChanged = $derived(!!changes && changes.variant.slug !== undefined);
	let descriptionChanged = $derived(!!changes && changes.variant.description !== undefined);
	let authorsChanged = $derived(!!changes && changes.authors !== undefined);
	let tagsChanged = $derived(!!changes && changes.tags !== undefined);
	let canonicalChanged = $derived(!!changes && changes.post.canonical_url !== undefined);
	let coverImageChanged = $derived(!!changes && changes.post.featured_image_url !== undefined);
	let featuredChanged = $derived(!!changes && changes.post.is_featured !== undefined);
	let publishTimeChanged = $derived(!!changes && changes.variant.published_at !== undefined);
	let codeHeadChanged = $derived(!!changes && changes.post.code_head !== undefined);
	let codeFootChanged = $derived(!!changes && changes.post.code_foot !== undefined);

	function authorName(user: User) {
		return user.variants.find((v) => v.language_id === primaryLanguage.id)?.name || 'Unnamed';
	}

	function inList<T extends { id: number }>(item: T, list: T[]) {
		return list.some((i) => i.id === item.id);
	}

	function changeColor<T extends { id: number }>(item: T, oldList: T[], newList: T[]) {
		if (inList(item, oldList) && inList(item, newList)) return 'default';
		if (inList(item, oldList)) return 'red';
		return 'green';
	}

	let authorUnion = $derived.by(() => {
		const all: User[] = [];
		for (const a of $postOriginalStore?.authors ?? []) all.push(a);
		for (const a of $postStore?.authors ?? []) if (!inList(a, all)) all.push(a);
		return all;
	});

	let tagUnion = $derived.by(() => {
		const all: TagType[] = [];
		for (const t of $postOriginalStore?.tags ?? []) all.push(t);
		for (const t of $postStore?.tags ?? []) if (!inList(t, all)) all.push(t);
		return all;
	});

	function formatTime(unix: number | null) {
		return unix
			? dayjs.unix(unix).format('YYYY-MM-DD HH:mm')
			: i18n.t('console.postEditor.update.none');
	}
</script>

<div class="summary">
	<div class="top">
		<div class="title-row">
			<div class="title">{i18n.t('console.postEditor.publish.summary.title')}</div>
			{#if errorCount > 0}
				<Tag size="x-small" color="red">
					{#snippet start()}
						<IconExclamationTriangle size={10} />
					{/snippet}
					{errorCount}
				</Tag>
			{/if}
			{#if warningCount > 0}
				<Tag size="x-small" color="orange">
					{#snippet start()}
						<IconExclamationCircle size={10} />
					{/snippet}
					{warningCount}
				</Tag>
			{/if}
		</div>
		{#if onEditSettings}
			<div class="settings">
				<Button variant="invisible" color="input" size="small" on:click={onEditSettings}>
					{#snippet start()}
						<IconGear size={12} />
					{/snippet}
					{i18n.t('console.postEditor.publish.summary.settings')}
				</Button>
			</div>
		{/if}
	</div>

	<div class="content">
		<div class="split" class:has-error={issues.suggestionsError}>
			<span>{i18n.t('console.postEditor.publish.summary.content')}</span>
			<div class="content-cell">
				<div class="content-main">
					{#if diff}
						<span class="value">
							<ContentChange
								contentOld={$postVariantOriginalStore.content}
								contentNew={$documentStore?.checkpoint_content ?? null}
								diff={contentChanged}
							/>
						</span>
					{:else}
						<span class="value">
							{i18n.t('console.postEditor.publish.summary.words', { count: stats.wordCount })}
							{#if stats.wordCount > 0}
								· {i18n.t('console.postEditor.publish.summary.readTime', {
									minutes: stats.readingMinutes
								})}
							{/if}
						</span>
					{/if}
					{#if issues.suggestionsError}
						<Validation state="error">{i18n.t(issues.suggestionsError)}</Validation>
					{/if}
				</div>
				{#if diff && $postVariantStore.content}
					<button class="compare-link" onclick={() => (compareOpen = true)}>
						{i18n.t('console.postEditor.publishedNotice.compare')}
					</button>
				{/if}
			</div>
		</div>

		<div class="split" class:has-error={issues.titleError}>
			<span>{i18n.t('console.postEditor.publish.summary.titleLabel')}</span>
			<div class="field-cell">
				<div class="field-main">
					{#if diff && titleChanged}
						<span class="value">
							<Diff
								strOld={$postVariantOriginalStore.title || ''}
								strNew={$postVariantStore.title || ''}
							/>
						</span>
					{:else if $postVariantStore.title}
						<span class="value">{$postVariantStore.title}</span>
					{/if}
					{#if issues.titleError}
						<Validation state="error">{i18n.t(issues.titleError)}</Validation>
					{/if}
				</div>
				{#if diff && !titleChanged}
					<span class="no-changes">{i18n.t('console.postEditor.update.noChanges')}</span>
				{/if}
			</div>
		</div>

		<div class="split" class:has-warning={issues.slugWarning}>
			<span>{i18n.t('console.postEditor.publish.summary.slug')}</span>
			<div class="field-cell">
				<div class="field-main">
					{#if diff && slugChanged}
						<span class="value">
							<Diff
								strOld={$postVariantOriginalStore.slug || ''}
								strNew={$postVariantStore.slug || ''}
							/>
						</span>
					{:else if $postVariantStore.slug}
						<span class="value">{$postVariantStore.slug}</span>
					{/if}
					{#if issues.slugWarning}
						<Validation state="warning">{i18n.t(issues.slugWarning)}</Validation>
					{/if}
				</div>
				{#if diff && !slugChanged}
					<span class="no-changes">{i18n.t('console.postEditor.update.noChanges')}</span>
				{/if}
			</div>
		</div>

		<div class="split" class:has-warning={issues.descriptionWarning}>
			<span>{i18n.t('console.postEditor.publish.summary.description')}</span>
			<div class="field-cell">
				<div class="field-main">
					{#if diff && descriptionChanged}
						<span class="value">
							<Diff
								strOld={$postVariantOriginalStore.description || ''}
								strNew={$postVariantStore.description || ''}
							/>
						</span>
					{:else if $postVariantStore.description}
						<span class="value">{$postVariantStore.description}</span>
					{/if}
					{#if issues.descriptionWarning}
						<Validation state="warning">{i18n.t(issues.descriptionWarning)}</Validation>
					{/if}
				</div>
				{#if diff && !descriptionChanged}
					<span class="no-changes">{i18n.t('console.postEditor.update.noChanges')}</span>
				{/if}
			</div>
		</div>

		<div class="split">
			<span>{i18n.t('console.postEditor.publish.summary.author')}</span>
			<div>
				{#if diff && authorsChanged}
					<div class="chips">
						{#each authorUnion as author (author.id)}
							{@const color = changeColor(author, $postOriginalStore.authors, $postStore.authors)}
							<Tag size="x-small" {color}>
								<span class="chip-inner">
									{#if color === 'green'}
										<IconPlus size={11} />
									{:else if color === 'red'}
										<IconDash size={11} />
									{/if}
									{authorName(author)}
								</span>
							</Tag>
						{/each}
					</div>
				{:else if $postStore.authors.length}
					<div class="chips">
						{#each $postStore.authors as author (author.id)}
							<AuthorTag user={author} size="x-small" />
						{/each}
					</div>
				{:else}
					<Text light small>{i18n.t('console.postEditor.publish.summary.noAuthors')}</Text>
				{/if}
			</div>
		</div>

		<div class="split">
			<span>{i18n.t('console.postEditor.publish.summary.tags')}</span>
			<div>
				{#if diff && tagsChanged}
					<div class="chips">
						{#each tagUnion as tag (tag.id)}
							{@const color = changeColor(tag, $postOriginalStore.tags, $postStore.tags)}
							<Tag size="x-small" {color}>
								<span class="chip-inner">
									{#if color === 'green'}
										<IconPlus size={11} />
									{:else if color === 'red'}
										<IconDash size={11} />
									{/if}
									{tag.variants[0]?.name || tag.slug}
								</span>
							</Tag>
						{/each}
					</div>
				{:else if $postStore.tags.length}
					<div class="chips">
						{#each $postStore.tags as tag (tag.id)}
							<TagChip {tag} size="x-small" />
						{/each}
					</div>
				{:else}
					<Text light small>{i18n.t('console.postEditor.publish.summary.noTags')}</Text>
				{/if}
			</div>
		</div>

		{#if !diff && $postStore.canonical_url}
			<div class="split">
				<span>{i18n.t('console.postEditor.publish.summary.canonicalUrl')}</span>
				<div>
					<span class="value">{$postStore.canonical_url}</span>
				</div>
			</div>
		{/if}

		{#if diff && canonicalChanged}
			<div class="split">
				<span>{i18n.t('console.postEditor.publish.summary.canonicalUrl')}</span>
				<div class="value change-row">
					<span>{$postOriginalStore.canonical_url || i18n.t('console.postEditor.update.none')}</span
					>
					<IconArrowRight size={12} />
					<strong>{$postStore.canonical_url || i18n.t('console.postEditor.update.none')}</strong>
				</div>
			</div>
		{/if}

		{#if diff && coverImageChanged}
			<div class="split">
				<span>{i18n.t('console.postEditor.update.coverImage')}</span>
				<div class="value change-row">
					{#if $postOriginalStore.featured_image_url}
						<img class="cover" src={$postOriginalStore.featured_image_url} alt="" />
					{:else}
						<span>{i18n.t('console.postEditor.update.noImage')}</span>
					{/if}
					<IconArrowRight size={12} />
					{#if $postStore.featured_image_url}
						<img class="cover" src={$postStore.featured_image_url} alt="" />
					{:else}
						<span>{i18n.t('console.postEditor.update.noImage')}</span>
					{/if}
				</div>
			</div>
		{/if}

		{#if diff && featuredChanged}
			<div class="split">
				<span>{i18n.t('console.postEditor.update.featured')}</span>
				<div class="value change-row">
					<span>
						{$postOriginalStore.is_featured
							? i18n.t('console.postEditor.update.yes')
							: i18n.t('console.postEditor.update.no')}
					</span>
					<IconArrowRight size={12} />
					<strong>
						{$postStore.is_featured
							? i18n.t('console.postEditor.update.yes')
							: i18n.t('console.postEditor.update.no')}
					</strong>
				</div>
			</div>
		{/if}

		{#if diff && publishTimeChanged}
			<div class="split">
				<span>{i18n.t('console.postEditor.update.publishTime')}</span>
				<div class="value change-row">
					<span>{formatTime($postVariantOriginalStore.published_at)}</span>
					<IconArrowRight size={12} />
					<strong>{formatTime($postVariantStore.published_at)}</strong>
				</div>
			</div>
		{/if}

		{#if diff && codeHeadChanged}
			<div class="split">
				<span>{i18n.t('console.postEditor.update.codeHead')}</span>
				<div class="value">{i18n.t('console.postEditor.update.changed')}</div>
			</div>
		{/if}

		{#if diff && codeFootChanged}
			<div class="split">
				<span>{i18n.t('console.postEditor.update.codeFoot')}</span>
				<div class="value">{i18n.t('console.postEditor.update.changed')}</div>
			</div>
		{/if}
	</div>
</div>

{#if compareOpen}
	<Compare
		leftContent={$postVariantStore.content || ''}
		rightContent={$documentStore?.checkpoint_content || $postVariantStore.content || ''}
		onclose={() => (compareOpen = false)}
	/>
{/if}

<style>
	.summary {
		border: 1px solid var(--border);
		border-radius: 20px;
		margin-bottom: 20px;
		overflow: hidden;
	}

	.top {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 6px 20px;
		border-bottom: 1px solid var(--border);
		background-color: var(--hover);
	}

	.title-row {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.title {
		font-size: 12px;
		font-weight: 600;
		text-transform: uppercase;
		color: var(--text-light);
	}

	.content {
		background-color: var(--box-background);
	}

	.split {
		display: grid;
		grid-template-columns: minmax(100px, 30%) 1fr;
		align-items: flex-start;
		gap: 10px;
		padding: 10px 20px;
		border-bottom: 1px solid var(--border);
	}

	.split > span {
		color: var(--text-light);
		font-size: 14px;
	}

	.split > div {
		min-width: 0;
	}

	.split.has-error {
		background-color: color-mix(in srgb, var(--red-light) 20%, transparent);
	}

	.split.has-warning {
		background-color: color-mix(in srgb, var(--orange-light) 20%, transparent);
	}

	.split :global(.validation) {
		margin-top: 5px;
	}

	.split :global(.icon) {
		display: inline-flex;
	}

	.value {
		font-size: 14px;
		word-break: break-word;
	}

	.content-cell,
	.field-cell {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 10px;
	}

	.content-main,
	.field-main {
		min-width: 0;
	}

	.compare-link {
		flex-shrink: 0;
		font-size: 14px;
		text-decoration: underline;
		color: var(--text-light);
	}
	.compare-link:hover {
		color: var(--text);
	}

	.no-changes {
		flex-shrink: 0;
		font-size: 13px;
		color: var(--text-light);
		white-space: nowrap;
	}

	.chip-inner {
		display: inline-flex;
		align-items: center;
		gap: 3px;
	}

	.change-row {
		display: flex;
		align-items: center;
		gap: 8px;
		flex-wrap: wrap;
	}

	.cover {
		max-width: 90px;
		max-height: 60px;
		border-radius: 5px;
	}

	.chips {
		display: flex;
		flex-wrap: wrap;
		gap: 5px;
	}
</style>
