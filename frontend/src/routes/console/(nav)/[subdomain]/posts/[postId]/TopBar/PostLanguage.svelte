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
	import {
		addPostVariantStore,
		postVariantLanguageStore,
		postStore,
		updatePostEditingStatusValue
	} from '../../postStore';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import type { Language } from '../../../../../lib/types';
	import { createPostVariant } from '../../postActions';
	import { goto } from '$app/navigation';

	let showDropdown = $state(false);
	let isCreatingVariant = $state(false);

	function handleSelect(lang: Language) {
		if (lang.id === $postVariantLanguageStore.id) return;
		showDropdown = false;

		// this triggers navigation
		// which will check for unsaved changes
		goto('?lang=' + lang.code, { replaceState: true });

		if (getVariantOfLanguage(lang.id)) {
			// has the variant
			updatePostEditingStatusValue('languageId', lang.id);
		} else {
			// create the variant
			const toastId = toast.loading(`Creating ${lang.name} variant...`);
			isCreatingVariant = true;

			createPostVariant($postStore.id, lang.id)
				.then((res) => {
					toast.success(`Created ${lang.name} variant`, { id: toastId });
					addPostVariantStore(res);
					updatePostEditingStatusValue('languageId', lang.id);
				})
				.catch(() => {
					toast.error(`Failed to create ${lang.name} variant`, { id: toastId });
				})
				.finally(() => {
					isCreatingVariant = false;
				});
		}
	}

	function getVariantOfLanguage(languageId: number) {
		return $postStore.variants.find((variant) => variant.language_id === languageId);
	}
</script>

{#if $languagesStore.length}
	<div class="wrap">
		<Dropdown bind:show={showDropdown} align="end" width={250}>
			{#snippet trigger()}
				<Button color="input" disabled={isCreatingVariant} size="small">
					{$postVariantLanguageStore.name}
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
										{getVariantOfLanguage(language.id)?.status || 'Missing'}
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
