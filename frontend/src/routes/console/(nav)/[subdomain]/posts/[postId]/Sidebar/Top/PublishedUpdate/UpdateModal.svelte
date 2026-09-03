<script lang="ts">
	import FeaturedChange from './Changes/FeaturedChange.svelte';
	import { postVariantLanguageStore } from '../../../../postStore';
	import {
		Button,
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
	import { getI18n } from '../../../../../../../lib/i18n';
	import { hasPendingSuggestions } from '../../../../../../../lib/prosemirror/suggestions';

	const i18n = getI18n();

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

	let pendingSuggestions = $derived(
		hasPendingSuggestions($documentStore?.checkpoint_content ?? null)
	);

	let disabled = $derived(
		(changes.variant.slug !== undefined && (changes.variant.slug || '').trim() === '') ||
			contentUpdatedAtTooEarly ||
			pendingSuggestions
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

		toast.success(i18n.t('console.postEditor.update.updated'));

		finishUpdating();
	}
</script>

<Modal
	bind:show
	title={i18n.t('console.postEditor.update.modalTitle')}
	size="medium"
	loading={isLoading}
>
	<div class="note">{i18n.t('console.postEditor.update.intro')}</div>

	{#if pendingSuggestions}
		<div class="note">
			<Validation state="error"
				>{i18n.t('console.postEditor.publish.issues.pendingSuggestions')}</Validation
			>
		</div>
	{/if}

	<div class="diff">
		<span>{i18n.t('console.postEditor.update.showDifference')}</span>
		<Switch bind:checked={diff} />
	</div>

	{#if changes.variant.content}
		<SplitControl label={i18n.t('console.postEditor.update.content')}>
			<ContentChange
				contentOld={$postVariantOriginalStore.content}
				contentNew={$documentStore.checkpoint_content}
				{diff}
			/>

			<div class="content-updated-at">
				<span style="display:inline-flex;align-items:center;gap:5px;">
					{i18n.t('console.postEditor.update.setCustomUpdatedTime')}
				</span>
				<Switch bind:checked={setCustomContentUpdatedAt} />
			</div>

			{#if setCustomContentUpdatedAt}
				<div style="margin-top:10px;">
					<TextInput block type="datetime-local" bind:value={customContentUpdatedAt} />

					{#if contentUpdatedAtTooEarly}
						<div style="margin-top:5px;">
							<Validation state="error"
								>{i18n.t('console.postEditor.update.updatedAtTooEarly')}</Validation
							>
						</div>
					{/if}
				</div>
			{/if}
		</SplitControl>
	{/if}

	{#if changes.variant.slug !== undefined}
		<SplitControl label={i18n.t('console.postEditor.update.slug')}>
			{#if diff}
				<Diff strOld={$postVariantOriginalStore.slug || ''} strNew={$postVariantStore.slug || ''} />
			{:else}
				<span>{$postVariantStore.slug}</span>
			{/if}

			<div style="margin-top:15px;">
				{#if (changes.variant.slug || '').trim() === ''}
					<Validation state="error">{i18n.t('console.postEditor.update.slugEmpty')}</Validation>
				{/if}
				{#if slugGetInvalidCharater(changes.variant.slug || '')}
					<Validation state="error"
						>{i18n.t('console.postEditor.update.slugInvalidChar', {
							char: slugGetInvalidCharater(changes.variant.slug || '')
						})}</Validation
					>
				{/if}
			</div>
			<div class="auto-redirects">
				<span style="display:inline-flex;align-items:center;gap:5px;">
					{i18n.t('console.postEditor.update.createRedirect')}
					<Tooltip text={i18n.t('console.postEditor.update.createRedirectTooltip')}>
						<IconInfoCircleFill />
					</Tooltip>
				</span>
				<Switch bind:checked={redirectOnSlugChange} />
			</div>
		</SplitControl>
	{/if}

	{#if changes.variant.description !== undefined}
		<SplitControl label={i18n.t('console.postEditor.update.description')}>
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
		<SplitControl label={i18n.t('console.postEditor.update.coverImage')}>
			<CoverImageChange
				featuredImageOld={$postOriginalStore.featured_image_url}
				featuredImageNew={$postStore.featured_image_url}
				{diff}
			/>
		</SplitControl>
	{/if}

	{#if changes.variant.published_at !== undefined}
		<SplitControl label={i18n.t('console.postEditor.update.publishTime')}>
			{#if diff}
				{$postVariantOriginalStore.published_at
					? dayjs.unix($postVariantOriginalStore.published_at).format('YYYY-MM-DD HH:mm:ss')
					: i18n.t('console.postEditor.update.none')}
				<span> → </span>
				<strong>
					{$postVariantStore.published_at
						? dayjs.unix($postVariantStore.published_at).format('YYYY-MM-DD HH:mm:ss')
						: i18n.t('console.postEditor.update.none')}
				</strong>
			{:else}
				<span>
					{$postVariantStore.published_at
						? dayjs.unix($postVariantStore.published_at).format('YYYY-MM-DD HH:mm:ss')
						: i18n.t('console.postEditor.update.none')}
				</span>
			{/if}
		</SplitControl>
	{/if}

	{#if changes.post.is_featured !== undefined}
		<SplitControl label={i18n.t('console.postEditor.update.featured')}>
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
		<SplitControl label={i18n.t('console.postEditor.update.codeHead')}
			>{i18n.t('console.postEditor.update.changed')}</SplitControl
		>
	{/if}

	{#if changes.post.code_foot !== undefined}
		<SplitControl label={i18n.t('console.postEditor.update.codeFoot')}
			>{i18n.t('console.postEditor.update.changed')}</SplitControl
		>
	{/if}

	{#snippet footer()}
		<Button variant="invisible" on:click={() => (show = false)}
			>{i18n.t('console.common.cancel')}</Button
		>

		<Button on:click={handleUpdate} {disabled}>{i18n.t('console.postEditor.update.button')}</Button>
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
