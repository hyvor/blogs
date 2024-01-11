<script lang="ts" context="module">
    export type AcceptableTypes = Blog | Tag | Navigation;
    export type AcceptableTypesNames = 'blog' | 'tag' | 'navigation';
</script>

<script lang="ts" generics="T extends AcceptableTypes">

	import VariantCreator from "./VariantCreator.svelte";
	import { languagesStore } from "../../../../lib/stores/languagesStore";
	import { SplitControl, TextInput } from "@hyvor/design/components";
	import type { Blog, Navigation, Tag } from "../../../../lib/types";
    import { createEventDispatcher } from "svelte";

    export let type: AcceptableTypesNames;
    export let obj : T;
    export let key: keyof T['variants'][number];

    export let label: string;
    export let caption = '';
    export let maxlength : number | undefined = undefined;

    $: hasVariant = (languageId: number) => {
        return obj.variants.some(v => v.language_id === languageId);
    }

    function getVariantValue(languageId: number) {
        const variant = obj.variants.find(v => v.language_id === languageId);
        if (variant) {
            return (variant as any)[key];
        }
        return '';
    }

    const dispatch = createEventDispatcher();

    function handleValueChange(languageId: number, e: any) {
        dispatch('change', {
            languageId,
            value: e.target.value
        });
    }

</script>

<SplitControl
    label={label}
    caption={caption}
>

    <div slot="nested">
        {#each $languagesStore as language, index}
            <SplitControl
                label={language.name}
                caption={language.code}
            >
                {#if hasVariant(language.id)}
                    <TextInput
                        value={getVariantValue(language.id)}
                        on:input={e => handleValueChange(language.id, e)}
                        {maxlength}
                        block
                    />
                {:else}
                    <VariantCreator 
                        {language}
                        {obj}
                        {type}    
                        on:variantCreate
                    />
                {/if}
            </SplitControl>
        {/each}
    </div>

</SplitControl>