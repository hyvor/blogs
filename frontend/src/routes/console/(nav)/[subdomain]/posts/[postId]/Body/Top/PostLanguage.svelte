<script lang="ts">
	import { ActionList, ActionListItem, Button, Dropdown, Text, toast } from "@hyvor/design/components";
    import { languagesStore } from "../../../../../../lib/stores/languagesStore";
	import { addPostVariantStore, postLanguageStore, postStore, updatePostEditingStatusValue } from "../../../postStore";
	import { IconCaretDown } from "@hyvor/icons";
	import type { Language } from "../../../../../../lib/types";
	import { createPostVariant } from "../../../postActions";
	import { goto } from "$app/navigation";

    let showDropdown = false;
    let isCreatingVariant = false;

    function handleSelect(lang: Language) {
        if (lang.id === $postLanguageStore.id) return;
        showDropdown = false;

        // this triggers navigation
        // which will check for unsaved changes
        goto('?lang=' + lang.code, {replaceState: true});

        if (getVariantOfLanguage(lang.id)) {
            // has the variant
            updatePostEditingStatusValue('languageId', lang.id);
        } else {
            // create the variant
            const toastId = toast.loading(`Creating ${lang.name} variant...`);
            isCreatingVariant = true;

            createPostVariant($postStore.id, lang.id)
                .then(res => {
                    toast.success(`Created ${lang.name} variant`, {id: toastId });
                    addPostVariantStore(res);
                    updatePostEditingStatusValue('languageId', lang.id);
                })
                .catch(() => {
                    toast.error(`Failed to create ${lang.name} variant`, {id: toastId });
                })
                .finally(() => {
                    isCreatingVariant = false;
                });
        }
    }

    function getVariantOfLanguage(languageId: number) {
        return $postStore.variants.find(variant => variant.language_id === languageId);
    }

</script>

{#if $languagesStore.length}

    <div class="wrap">

        <Dropdown 
            bind:show={showDropdown}
            align="end"
            width={250}
        >

            <Button
                slot="trigger"
                color="input"
                disabled={isCreatingVariant}
            >
                {$postLanguageStore.name}
                <IconCaretDown slot="end" size={12} />
            </Button>

            <ActionList 
                slot="content"
            >

                {#each $languagesStore as language}
                    <ActionListItem
                        on:select={() => handleSelect(language)}
                        disabled={language.id === $postLanguageStore.id}
                        style="
                            {language.id === $postLanguageStore.id && 'background-color:var(--accent-light-mid)'}
                        "
                    >
                        {language.name}

                        <span slot="end" class="status">
                            <Text small light>
                                {getVariantOfLanguage(language.id)?.status || 'Missing'}
                            </Text>
                        </span>

                    </ActionListItem>
                {/each}

            </ActionList>

        </Dropdown>

    </div>

{/if}

<style>
    .wrap {
        padding: 10px;
    }
    .wrap :global(.dropdown .content-wrap) {
        z-index: 11!important;
    }
    .status {
        text-transform: capitalize;
    }
</style>