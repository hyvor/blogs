<script lang="ts">
	import { postVariantLanguageStore } from '../../../../postStore';
	import { Button, Modal, Switch, Tooltip, Validation, toast } from '@hyvor/design/components';
	import {
		documentStore,
		postOriginalStore,
		postSidebarStore,
		postStore,
		postVariantOriginalStore,
		postVariantStore
	} from '../../../../postStore';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { getPublishedChanges } from './published-changes';
	import {
		updatePost,
		updatePostAuthors,
		updatePostTags,
		updatePostVariant,
		updatePublishedPostContent
	} from '../../../../postActions';
	import PublishSummary from '../PublishSummary.svelte';
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
	let redirectOnSlugChange = $state(true);

	$effect(() => {
		void $postStore;
		void $postOriginalStore;
		void $postVariantStore;
		void $postVariantOriginalStore;
		void $documentStore;

		changes = getPublishedChanges();
	});

	let pendingSuggestions = $derived(
		hasPendingSuggestions($documentStore?.checkpoint_content ?? null)
	);

	let slugChanged = $derived(changes.variant.slug !== undefined);
	let slugEmpty = $derived(slugChanged && (changes.variant.slug || '').trim() === '');
	let slugInvalidChar = $derived(
		slugChanged ? slugGetInvalidCharater(changes.variant.slug || '') : null
	);

	let disabled = $derived(slugEmpty || !!slugInvalidChar || pendingSuggestions);

	let isLoading = $state(false);
	let updated: null | { url: string } = $state(null);

	async function handleUpdate() {
		isLoading = true;

		try {
			if (changes.variant.content !== undefined) {
				await updatePublishedPostContent();
			}

			const variantChanges = { ...changes.variant };
			delete variantChanges.content;

			if (Object.keys(variantChanges).length) {
				await updatePostVariant(
					{
						language_id: $postVariantLanguageStore.id,
						...variantChanges,
						redirect_on_slug_change: redirectOnSlugChange
					},
					true,
					['url']
				);
			}

			if (Object.keys(changes.post).length) {
				await updatePost({ ...changes.post });
			}

			if (changes.authors !== undefined) {
				await updatePostAuthors(changes.authors);
			}

			if (changes.tags !== undefined) {
				await updatePostTags(changes.tags);
			}
		} catch (e: any) {
			isLoading = false;
			return toast.error(e.message);
		}

		isLoading = false;
		updated = { url: $postVariantStore.url };
	}

	function openSettings() {
		show = false;
		$postSidebarStore = 'settings';
	}
</script>

<Modal
	bind:show
	title={i18n.t('console.postEditor.update.modalTitle')}
	size={updated ? 'small' : 'medium'}
	loading={isLoading}
	bare={updated !== null}
	onclose={() => {
		if (updated !== null) {
			updated = null;
		}
	}}
>
	{#if updated === null}
		<div class="note">{i18n.t('console.postEditor.update.intro')}</div>

		{#if pendingSuggestions}
			<div class="note">
				<Validation state="error"
					>{i18n.t('console.postEditor.publish.issues.pendingSuggestions')}</Validation
				>
			</div>
		{/if}

		<PublishSummary diff onEditSettings={openSettings} />

		{#if slugChanged}
			<div class="auto-redirects">
				<span>
					{i18n.t('console.postEditor.update.createRedirect')}
					<Tooltip text={i18n.t('console.postEditor.update.createRedirectTooltip')}>
						<IconInfoCircleFill />
					</Tooltip>
				</span>
				<Switch bind:checked={redirectOnSlugChange} />
			</div>

			{#if slugEmpty}
				<Validation state="error">{i18n.t('console.postEditor.update.slugEmpty')}</Validation>
			{/if}
			{#if slugInvalidChar}
				<Validation state="error"
					>{i18n.t('console.postEditor.update.slugInvalidChar', {
						char: slugInvalidChar
					})}</Validation
				>
			{/if}
		{/if}
	{:else}
		<div class="published">
			<div class="icon">
				<IconCheck size={26} />
			</div>
			<div class="title">
				{i18n.t('console.postEditor.update.updated')}
			</div>
			<div class="button">
				<Button as="a" href={updated.url} target="_blank" color="accent">
					{i18n.t('console.postEditor.publish.viewPost')}
					{#snippet end()}
						<IconBoxArrowUpRight size={10} />
					{/snippet}
				</Button>
			</div>
		</div>
	{/if}

	{#snippet footer()}
		{#if updated === null}
			<Button variant="invisible" on:click={() => (show = false)}
				>{i18n.t('console.common.cancel')}</Button
			>

			<Button on:click={handleUpdate} {disabled}
				>{i18n.t('console.postEditor.update.button')}</Button
			>
		{/if}
	{/snippet}
</Modal>

<style>
	.note {
		margin-bottom: 15px;
	}
	.auto-redirects {
		font-size: 14px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
	}
	.auto-redirects span {
		display: inline-flex;
		align-items: center;
		gap: 5px;
	}

	.published {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 15px;
		padding: 25px 0;
	}

	.published .icon {
		width: 35px;
		height: 35px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		background-color: var(--green-light);
		color: var(--green-dark);
	}

	.published .title {
		font-size: 16px;
		font-weight: 600;
	}

	.published .button {
		margin-top: 5px;
	}
</style>
