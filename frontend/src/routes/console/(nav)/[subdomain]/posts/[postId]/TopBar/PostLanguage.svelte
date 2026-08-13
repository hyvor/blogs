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

	function getVariantStatus(languageId: number) {
		return $postStore.variant_statuses.find((variant) => variant.language_id === languageId)
			?.status;
	}
</script>

{#if $languagesStore.length}
	<div class="wrap">
		<Dropdown bind:show={showDropdown} align="center" width={250}>
			{#snippet trigger()}
				<Button color="input" disabled={creatingLanguageId !== null} size="small">
					{$postVariantLanguageStore.name}

					&nbsp;
					<PostStatusTag
						status={$postVariantStore.status}
						showIcon={false}
						size="x-small"
					/>

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

							{#snippet end()}
								<span class="status">
									<Text small light>
										{creatingLanguageId === language.id
											? 'Creating...'
											: getVariantStatus(language.id) || 'Not created'}
									</Text>
								</span>
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
	.status {
		text-transform: capitalize;
	}
</style>
