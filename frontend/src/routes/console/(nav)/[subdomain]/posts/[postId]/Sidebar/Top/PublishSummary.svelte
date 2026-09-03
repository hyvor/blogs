<script lang="ts">
	import { onMount } from 'svelte';
	import { Button, Tag, Text, Validation } from '@hyvor/design/components';
	import IconGear from '@hyvor/icons/IconGear';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';
	import IconExclamationTriangle from '@hyvor/icons/IconExclamationTriangle';
	import { postStore, postVariantStore } from '../../../postStore';
	import AuthorTag from '../../../AuthorTag.svelte';
	import TagChip from '../../../TagChip.svelte';
	import { emptyPublishIssues, getPublishIssues, type PublishIssues } from './publishIssues';
	import { emptyContentStats, getContentStats, type ContentStats } from './contentStats';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		onEditSettings: () => void;
		onIssues?: (issues: PublishIssues) => void;
	}

	let { onEditSettings, onIssues }: Props = $props();

	let issues: PublishIssues = $state(emptyPublishIssues);
	let stats: ContentStats = $state(emptyContentStats);

	onMount(() => {
		issues = getPublishIssues();
		onIssues?.(issues);

		stats = getContentStats();
	});

	let errorCount = $derived((issues.titleError ? 1 : 0) + (issues.suggestionsError ? 1 : 0));
	let warningCount = $derived((issues.slugWarning ? 1 : 0) + (issues.descriptionWarning ? 1 : 0));
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
		<div class="settings">
			<Button variant="invisible" color="input" size="small" on:click={onEditSettings}>
				{#snippet start()}
					<IconGear size={12} />
				{/snippet}
				{i18n.t('console.postEditor.publish.summary.settings')}
			</Button>
		</div>
	</div>

	<div class="content">
		<div class="split" class:has-error={issues.suggestionsError}>
			<span>{i18n.t('console.postEditor.publish.summary.content')}</span>
			<div>
				<span class="value">
					{i18n.t('console.postEditor.publish.summary.words', { count: stats.wordCount })}
					{#if stats.wordCount > 0}
						· {i18n.t('console.postEditor.publish.summary.readTime', {
							minutes: stats.readingMinutes
						})}
					{/if}
				</span>
				{#if issues.suggestionsError}
					<Validation state="error">{i18n.t(issues.suggestionsError)}</Validation>
				{/if}
			</div>
		</div>

		<div class="split" class:has-error={issues.titleError}>
			<span>{i18n.t('console.postEditor.publish.summary.titleLabel')}</span>
			<div>
				{#if $postVariantStore.title}
					<span class="value">
						{$postVariantStore.title}
					</span>
				{/if}
				{#if issues.titleError}
					<Validation state="error">{i18n.t(issues.titleError)}</Validation>
				{/if}
			</div>
		</div>

		<div class="split" class:has-warning={issues.slugWarning}>
			<span>{i18n.t('console.postEditor.publish.summary.slug')}</span>
			<div>
				{#if $postVariantStore.slug}
					<span class="value">
						{$postVariantStore.slug}
					</span>
				{/if}
				{#if issues.slugWarning}
					<Validation state="warning">{i18n.t(issues.slugWarning)}</Validation>
				{/if}
			</div>
		</div>

		<div class="split" class:has-warning={issues.descriptionWarning}>
			<span>{i18n.t('console.postEditor.publish.summary.description')}</span>
			<div>
				{#if $postVariantStore.description}
					<span class="value">
						{$postVariantStore.description}
					</span>
				{/if}
				{#if issues.descriptionWarning}
					<Validation state="warning">{i18n.t(issues.descriptionWarning)}</Validation>
				{/if}
			</div>
		</div>

		<div class="split">
			<span>{i18n.t('console.postEditor.publish.summary.author')}</span>
			<div>
				{#if $postStore.authors.length}
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
				{#if $postStore.tags.length}
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

		{#if $postStore.canonical_url}
			<div class="split">
				<span>{i18n.t('console.postEditor.publish.summary.canonicalUrl')}</span>
				<div>
					<span class="value">{$postStore.canonical_url}</span>
				</div>
			</div>
		{/if}
	</div>
</div>

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

	.chips {
		display: flex;
		flex-wrap: wrap;
		gap: 5px;
	}
</style>
