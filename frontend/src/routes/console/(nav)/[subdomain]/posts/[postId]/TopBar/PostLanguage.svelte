<script lang="ts">
	import {
		ActionList,
		ActionListItem,
		Button,
		Dropdown,
		Text,
		toast
	} from '@hyvor/design/components';
	import { languagesStore } from '../../../../../lib/stores/languagesStore';
	import { postVariantLanguageStore, postStore, postVariantStore } from '../../postStore';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import type { Language } from '../../../../../lib/types';
	import { createPostVariant } from '../../postActions';
	import { goto } from '$app/navigation';
	import PostStatusTag from '../../PostStatusTag.svelte';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import dayjs from 'dayjs';

	let showDropdown = $state(false);
	let creatingLanguageId: number | null = $state(null);

	async function handleSelect(lang: Language) {
		if (lang.id === $postVariantLanguageStore.id) return;
		showDropdown = false;

		// create the variant if it doesn't exist
		if (!getVariantStatus(lang.id)) {
			creatingLanguageId = lang.id;

			try {
				await createPostVariant($postStore.id, lang.id);
				creatingLanguageId = null;
			} catch (error) {
				toast.error(
					`Failed to create ${lang.name} variant: ${error instanceof Error ? error.message : String(error)}`
				);
				creatingLanguageId = null;
				return;
			}
		}

		// take the user to the variant page
		goto(consoleUrlWithBlog(`/posts/${$postStore.id}/${lang.code.toLowerCase()}`));
	}

	function getVariant(languageId: number) {
		return $postStore.variants.find((v) => v.language_id === languageId);
	}

	function getVariantStatus(languageId: number) {
		return getVariant(languageId)?.status;
	}

	// content_updated_at reflects when the content itself last changed (null while draft);
	// fall back to updated_at, which changes on any field update
	function getVariantLastUpdated(languageId: number) {
		const variant = getVariant(languageId);
		if (!variant) return null;
		return variant.content_updated_at ?? variant.updated_at;
	}

	function getVariantWords(languageId: number) {
		return getVariant(languageId)?.words ?? null;
	}

	function formatWords(words: number) {
		return `${words.toLocaleString()} word${words === 1 ? '' : 's'}`;
	}
</script>

{#if $languagesStore.length}
	<div class="wrap">
		<Dropdown bind:show={showDropdown} align="center" width={290}>
			{#snippet trigger()}
				<Button color="input" disabled={creatingLanguageId !== null} size="small">
					{$postVariantLanguageStore.name}

					&nbsp;
					<PostStatusTag status={$postVariantStore.status} showIcon={false} size="x-small" />

					{#snippet end()}
						<IconCaretDown size={12} />
					{/snippet}
				</Button>
			{/snippet}

			{#snippet content()}
				<ActionList>
					{#each $languagesStore as language}
						<ActionListItem
							on:select={() => handleSelect(language)}
							disabled={language.id === $postVariantLanguageStore.id}
							style="
                                {language.id === $postVariantLanguageStore.id &&
								'background-color:var(--accent-light-mid)'}
                            "
						>
							{language.name}

							{#snippet description()}
								{#if creatingLanguageId === language.id}
									<Text small light>Creating...</Text>
								{:else if !getVariantStatus(language.id)}
									<Text small light>Not created</Text>
								{:else}
									{@const words = getVariantWords(language.id)}
									{@const updatedAt = getVariantLastUpdated(language.id)}
									<Text small light>
										{#if words}{formatWords(words)}{/if}
										{#if words && updatedAt}&nbsp;·&nbsp;{/if}
										{#if updatedAt}{dayjs.unix(updatedAt).fromNow()}{/if}
									</Text>
								{/if}
							{/snippet}

							{#snippet end()}
								{#if creatingLanguageId !== language.id}
									{@const status = getVariantStatus(language.id)}
									{#if status}
										<PostStatusTag {status} size="x-small" />
									{/if}
								{/if}
							{/snippet}
						</ActionListItem>
					{/each}
				</ActionList>
			{/snippet}
		</Dropdown>
	</div>
{/if}

<style>
	.wrap {
		margin-right: 10px;
	}
	.wrap :global(.dropdown .content-wrap) {
		z-index: 11 !important;
	}
</style>
