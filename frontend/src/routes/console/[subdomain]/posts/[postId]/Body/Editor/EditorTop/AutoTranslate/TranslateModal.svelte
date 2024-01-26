<script lang="ts">
	import { ActionList, ActionListItem, Button, Callout, Dropdown, Modal, SplitControl, toast } from "@hyvor/design/components";
	import UpgradeRequired from "../../../../../../billing/UpgradeRequired.svelte";
	import { languagesStore, primaryLanguageStore } from "../../../../../../../lib/stores/languagesStore";
	import type { Language, PostVariant } from "../../../../../../../lib/types";
	import { IconCaretDown, IconExclamation, IconExclamationCircle } from "@hyvor/icons";
	import { increaseEditorVersion, postCurrentContentKey, postLanguageStore, postStore, postVariantStore, updatePostVariantStore } from "../../../../../postStore";
	import { DEEPL_SOURCE_LANGUAGES, DEEPL_TARGET_LANGUAGES, findMatchingLanguage } from "./deepl";
	import { autoTranslate } from "./autoTranslateActions";
	import { updatePostVariant } from "../../../../../postActions";
	import { tick } from "svelte";
    
    export let show: boolean;

    let sourceVariantLanguage: Language = $primaryLanguageStore;
    let sourceVariantDropdownShow = false;

    let sourceDeepLLanguage  = findMatchingLanguage(
        $primaryLanguageStore.code, 
        DEEPL_SOURCE_LANGUAGES
    );

    let targetDeepLLanguage = findMatchingLanguage(
        $postLanguageStore.code,
        DEEPL_TARGET_LANGUAGES
    );

    let dropdownSource = false;
    let dropdownTarget = false;
    
    function handleSourceClick(key: any) {
        sourceDeepLLanguage = key;
        dropdownSource = false;
    }

    function handleTargetClick(key: any) {
        targetDeepLLanguage = key;
        dropdownTarget = false;
    }

    let isTranslating = false;

    function handleTranslate() {
        isTranslating = true;

        const variant = $postStore.variants.find(v => v.language_id === sourceVariantLanguage.id)!;
        
        autoTranslate(
            sourceDeepLLanguage,
            targetDeepLLanguage,
            variant.content,
            variant.title,
            variant.description,
            variant.slug,
        ).then(res => {

            const updates = {
                title: res.title,
                description: res.description,
                [$postCurrentContentKey]: res.content,
            } as Partial<PostVariant>;

            if ($postVariantStore.slug === null) {
                updates.slug = res.slug;
            }

            updatePostVariantStore(updates);
            increaseEditorVersion();

            toast.success('Successfully translated');

            show = false;

        }).catch(e => {
            toast.error(e.message);
        })
        .finally(() => {
            isTranslating = false;
        })

    }

</script>

<Modal
    title="Auto-Translate"
    bind:show={show}
    footer={{
        confirm: {
            text: 'Translate'
        }
    }}
    closeOnEscape={false}
    closeOnOutsideClick={false}
    on:confirm={handleTranslate}
    loading={isTranslating}
>

    <UpgradeRequired
        trialAllowed={true}
        minPlan="growth"
    >

        <div slot="upgrade-text">
            Auto-translation is available in the Growth and higher plans. Upgrade now to easily translate your posts into multiple languages.
        </div>

        <div class="note">
            Automatically translate your posts using DeepL. Make sure to review the translations before publishing.
        </div>

        <SplitControl 
            label="Source Variant"
            caption="The variant you want to translate from"
        >

            { sourceVariantLanguage.name }

            <!-- <Dropdown bind:show={sourceVariantDropdownShow}>

                <Button 
                    color="input"
                    slot="trigger"
                    size="small"
                >
                    {sourceVariantLanguage.name}
                    <IconCaretDown slot="end" size={12} />
                </Button>

                <ActionList slot="content">
                    {#each $languagesStore as language (language.id)}
                        {#if language.id !== $postLanguageStore.id}
                            <ActionListItem
                                on:click={() => {
                                    sourceVariantLanguage = language
                                    sourceVariantDropdownShow = false;
                                }}
                            >
                                {language.name}
                            </ActionListItem>
                        {/if}
                    {/each}
                </ActionList>

            </Dropdown> -->

        </SplitControl>

        <SplitControl
            label="Source Language"
            caption="Choose the language of the source variant (29 supported)"
        >

            <Dropdown bind:show={dropdownSource}>

                <Button 
                    color="input"
                    slot="trigger"
                    size="small"
                >
                    { DEEPL_SOURCE_LANGUAGES[sourceDeepLLanguage] }
                    <IconCaretDown slot="end" size={12} />
                </Button>

                <ActionList 
                    slot="content"
                    style="max-height: 250px; overflow-y: auto;"
                >
                    {#each Object.entries(DEEPL_SOURCE_LANGUAGES) as [key, value] (key)}
                        <ActionListItem
                            on:click={() => handleSourceClick(key)}
                        >
                            {value}
                        </ActionListItem>
                    {/each}
                </ActionList>

            </Dropdown>

        </SplitControl>

        <SplitControl
            label="Target Language"
            caption="Choose the language you want to translate to (31 supported)"
        >

            <Dropdown bind:show={dropdownTarget}>

                <Button 
                    color="input"
                    slot="trigger"
                    size="small"
                >
                    { DEEPL_TARGET_LANGUAGES[targetDeepLLanguage] }
                    <IconCaretDown slot="end" size={12} />
                </Button>

                <ActionList 
                    slot="content"
                    style="max-height: 250px; overflow-y: auto;"
                >
                    {#each Object.entries(DEEPL_TARGET_LANGUAGES) as [key, value] (key)}
                        <ActionListItem
                            on:click={() => handleTargetClick(key)}
                        >
                            {value}
                        </ActionListItem>
                    {/each}
                </ActionList>

            </Dropdown>

        </SplitControl>

        <Callout type="warning" style="margin-top:10px;">
            <IconExclamationCircle slot="icon" />
            <div slot="title">
                Important
            </div>
            The title, content, description, and the slug of the <strong>{ $postLanguageStore.name } variant</strong> will be replaced with the translated version.
        </Callout>


    </UpgradeRequired>
</Modal>

<style>
    .note {
        margin-bottom: 15px;
        color:var(--text-light);
        font-size:14px;
    }
</style>