<script lang="ts">
	import { onMount } from 'svelte';
	import { Button, Text, Validation } from '@hyvor/design/components';
	import IconGear from '@hyvor/icons/IconGear';
	import { postStore, postVariantStore } from '../../../postStore';
	import AuthorTag from '../../../AuthorTag.svelte';
	import TagChip from '../../../TagChip.svelte';
	import { emptyPublishIssues, getPublishIssues, type PublishIssues } from './publishIssues';

	interface Props {
		onEditSettings: () => void;
		onIssues?: (issues: PublishIssues) => void;
	}

	let { onEditSettings, onIssues }: Props = $props();

	let issues: PublishIssues = $state(emptyPublishIssues);

	onMount(() => {
		issues = getPublishIssues();
		onIssues?.(issues);
	});
</script>

<div class="summary">
	<div class="top">
		<div class="title">Post Summary</div>
		<div class="settings">
			<Button variant="invisible" color="input" size="small" on:click={onEditSettings}>
				{#snippet start()}
					<IconGear size={12} />
				{/snippet}
				Settings
			</Button>
		</div>
	</div>

	<div class="content">
		<div class="split">
			<span>Title</span>
			<div>
				<span class="value" class:empty={!$postVariantStore.title}>
					{$postVariantStore.title || 'Untitled'}
				</span>
				{#if issues.titleError}
					<Validation state="error">{issues.titleError}</Validation>
				{/if}
			</div>
		</div>

		<div class="split">
			<span>Slug</span>
			<div>
				<span class="value" class:empty={!$postVariantStore.slug}>
					{$postVariantStore.slug || 'Auto-generated'}
				</span>
				{#if issues.slugWarning}
					<Validation state="warning">{issues.slugWarning}</Validation>
				{/if}
			</div>
		</div>

		<div class="split">
			<span>Description</span>
			<div>
				{#if $postVariantStore.description}
					<span class="value">
						{$postVariantStore.description}
					</span>
				{/if}
				{#if issues.descriptionWarning}
					<Validation state="warning">{issues.descriptionWarning}</Validation>
				{/if}
			</div>
		</div>

		<div class="split">
			<span>Author</span>
			<div>
				{#if $postStore.authors.length}
					<div class="chips">
						{#each $postStore.authors as author (author.id)}
							<AuthorTag user={author} size="x-small" />
						{/each}
					</div>
				{:else}
					<Text light small>No authors</Text>
				{/if}
			</div>
		</div>

		<div class="split">
			<span>Tags</span>
			<div>
				{#if $postStore.tags.length}
					<div class="chips">
						{#each $postStore.tags as tag (tag.id)}
							<TagChip {tag} size="x-small" />
						{/each}
					</div>
				{:else}
					<Text light small>No tags</Text>
				{/if}
			</div>
		</div>

		{#if $postStore.canonical_url}
			<div class="split">
				<span>Canonical URL</span>
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
		border-bottom: 1px solid #eee;
	}

	.split > span {
		color: var(--text-light);
		font-size: 14px;
	}

	.split > div {
		min-width: 0;
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

	.value.empty {
		color: var(--text-light);
		font-style: italic;
	}

	.chips {
		display: flex;
		flex-wrap: wrap;
		gap: 5px;
	}
</style>
