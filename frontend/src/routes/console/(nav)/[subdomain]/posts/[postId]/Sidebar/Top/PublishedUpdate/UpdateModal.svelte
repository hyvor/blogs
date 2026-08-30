<script lang="ts">
	import FeaturedChange from './Changes/FeaturedChange.svelte';
	import { postVariantLanguageStore } from '../../../../postStore';
	import {
		Button,
		ButtonGroup,
		Modal,
		SplitControl,
		Switch,
		TextInput,
		Tooltip,
		Validation,
		toast
	} from '@hyvor/design/components';
	import {
		documentStore,
		postOriginalStore,
		postStore,
		postVariantOriginalStore,
		postVariantStore
	} from '../../../../postStore';
	import Diff from '$lib/components/Diff/Diff.svelte';
	import dayjs from 'dayjs';
	import { finishUpdating, getPublishedChanges } from './published-changes';
	import ContentChange from './Changes/ContentChange.svelte';
	import {
		updatePost,
		updatePostAuthors,
		updatePostTags,
		updatePostVariant
	} from '../../../../postActions';
	import CoverImageChange from './Changes/CoverImageChange.svelte';
	import CanonicalUrlChange from './Changes/CanonicalUrlChange.svelte';
	import TagChanges from './Changes/TagChanges.svelte';
	import AuthorChanges from './Changes/AuthorChanges.svelte';
	import { slugGetInvalidCharater } from '../../Settings/slug';
	import IconInfoCircleFill from '@hyvor/icons/IconInfoCircleFill';
	interface Props {
		show?: boolean;
	}

	let { show = $bindable(false) }: Props = $props();

	let changes: ReturnType<typeof getPublishedChanges> = $state(getPublishedChanges());
	let diff = $state(true);
	let redirectOnSlugChange = $state(true);

	let setCustomContentUpdatedAt = $state(false);
	let customContentUpdatedAt = $state(dayjs().format('YYYY-MM-DDTHH:mm'));

	let contentUpdatedAtTooEarly = $derived(
		setCustomContentUpdatedAt &&
			$postVariantStore.published_at !== null &&
			dayjs(customContentUpdatedAt).unix() < $postVariantStore.published_at
	);

	$effect(() => {
		$postStore;
		$postOriginalStore;

		changes = getPublishedChanges();
	});

	let disabled = $derived(
		(changes.variant.slug !== undefined && (changes.variant.slug || '').trim() === '') ||
			contentUpdatedAtTooEarly
	);

	let isLoading = $state(false);

	async function handleUpdate() {
		isLoading = true;

		if (Object.keys(changes.variant).length) {
			try {
				await updatePostVariant({
					language_id: $postVariantLanguageStore.id,
					...changes.variant,
					redirect_on_slug_change: redirectOnSlugChange,
					...(setCustomContentUpdatedAt
						? { content_updated_at: dayjs(customContentUpdatedAt).unix() }
						: {})
				});
			} catch (e: any) {
				isLoading = false;
				return toast.error(e.message);
			}
		}

		if (Object.keys(changes.post).length) {
			try {
				await updatePost({
					...changes.post
				});
			} catch (e: any) {
				isLoading = false;
				return toast.error(e.message);
			}
		}

		if (changes.authors !== undefined) {
			try {
				await updatePostAuthors(changes.authors);
			} catch (e: any) {
				isLoading = false;
				return toast.error(e.message);
			}
		}

		if (changes.tags !== undefined) {
			try {
				await updatePostTags(changes.tags);
			} catch (e: any) {
				isLoading = false;
				return toast.error(e.message);
			}
		}

		isLoading = false;
		show = false;

		toast.success('Post updated successfully.');

		finishUpdating();
	}
</script>

<Modal bind:show title="Update Post" size="medium" loading={isLoading}>
	<div class="note">You are about to update the post. Please review the changes below.</div>

	<div class="diff">
		<span> Show Difference </span>
		<Switch bind:checked={diff} />
	</div>

	{#if changes.variant.content}
		<SplitControl label="Content">
			<ContentChange
				contentOld={$postVariantOriginalStore.content}
				contentNew={$documentStore.checkpoint_content}
				{diff}
			/>

			<div class="content-updated-at">
				<span style="display:inline-flex;align-items:center;gap:5px;">
					Set a custom updated time
				</span>
				<Switch bind:checked={setCustomContentUpdatedAt} />
			</div>

			{#if setCustomContentUpdatedAt}
				<div style="margin-top:10px;">
					<TextInput block type="datetime-local" bind:value={customContentUpdatedAt} />

					{#if contentUpdatedAtTooEarly}
						<div style="margin-top:5px;">
							<Validation state="error">Must be on or after the publish time.</Validation>
						</div>
					{/if}
				</div>
			{/if}
		</SplitControl>
	{/if}

	{#if changes.variant.slug !== undefined}
		<SplitControl label="Slug">
			{#if diff}
				<Diff strOld={$postVariantOriginalStore.slug || ''} strNew={$postVariantStore.slug || ''} />
			{:else}
				<span>{$postVariantStore.slug}</span>
			{/if}

			<div style="margin-top:15px;">
				{#if (changes.variant.slug || '').trim() === ''}
					<Validation state="error">Slug cannot be empty.</Validation>
				{/if}
				{#if slugGetInvalidCharater(changes.variant.slug || '')}
					<Validation state="error"
						>Slug cannot contain {slugGetInvalidCharater(changes.variant.slug || '')}.</Validation
					>
				{/if}
			</div>
			<div class="auto-redirects">
				<span style="display:inline-flex;align-items:center;gap:5px;">
					Create redirect
					<Tooltip
						text="Automatically create a permanent redirect from the old URL to the new URL."
					>
						<IconInfoCircleFill />
					</Tooltip>
				</span>
				<Switch bind:checked={redirectOnSlugChange} />
			</div>
		</SplitControl>
	{/if}

	{#if changes.variant.description !== undefined}
		<SplitControl label="Description">
			{#if diff}
				<Diff
					strOld={$postVariantOriginalStore.description || ''}
					strNew={$postVariantStore.description || ''}
				/>
			{:else}
				<span>{$postVariantStore.description}</span>
			{/if}
		</SplitControl>
	{/if}

	{#if changes.authors !== undefined}
		<AuthorChanges {diff} />
	{/if}

	{#if changes.tags !== undefined}
		<TagChanges {diff} />
	{/if}

	{#if changes.post.featured_image_url !== undefined}
		<SplitControl label="Cover Image">
			<CoverImageChange
				featuredImageOld={$postOriginalStore.featured_image_url}
				featuredImageNew={$postStore.featured_image_url}
				{diff}
			/>
		</SplitControl>
	{/if}

	{#if changes.variant.published_at !== undefined}
		<SplitControl label="Publish Time">
			{#if diff}
				{$postVariantOriginalStore.published_at
					? dayjs.unix($postVariantOriginalStore.published_at).format('YYYY-MM-DD HH:mm:ss')
					: 'None'}
				<span> → </span>
				<strong>
					{$postVariantStore.published_at
						? dayjs.unix($postVariantStore.published_at).format('YYYY-MM-DD HH:mm:ss')
						: 'None'}
				</strong>
			{:else}
				<span>
					{$postVariantStore.published_at
						? dayjs.unix($postVariantStore.published_at).format('YYYY-MM-DD HH:mm:ss')
						: 'None'}
				</span>
			{/if}
		</SplitControl>
	{/if}

	{#if changes.post.is_featured !== undefined}
		<SplitControl label="Featured">
			<FeaturedChange old={$postOriginalStore.is_featured} {diff} />
		</SplitControl>
	{/if}

	{#if changes.post.canonical_url !== undefined}
		<CanonicalUrlChange
			canonicalUrlOld={$postOriginalStore.canonical_url}
			canonicalUrlNew={$postStore.canonical_url}
			{diff}
		/>
	{/if}

	{#if changes.post.code_head !== undefined}
		<SplitControl label="Code Head">Changed</SplitControl>
	{/if}

	{#if changes.post.code_foot !== undefined}
		<SplitControl label="Code Foot">Changed</SplitControl>
	{/if}

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}>Cancel</Button>

			<Button on:click={handleUpdate} {disabled}>Update</Button>
		</ButtonGroup>
	{/snippet}
</Modal>

<style>
	.note {
		margin-bottom: 15px;
	}
	.diff {
		text-align: center;
		padding: 10px 15px;
		font-size: 14px;
		color: var(--text-light);
	}
	.diff span {
		margin-right: 10px;
	}
	.auto-redirects {
		font-size: 14px;
	}
	.auto-redirects span {
		margin-right: 10px;
	}
	.content-updated-at {
		font-size: 14px;
		margin-top: 10px;
	}
	.content-updated-at span {
		margin-right: 10px;
	}
</style>
