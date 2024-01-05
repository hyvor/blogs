<script lang="ts">
	import { Button, SplitControl, TextInput } from "@hyvor/design/components";
	import BlogSettingsSave from "./BlogSettingsSave.svelte";
	import { languagesStore } from "../../lib/stores/languagesStore";
	import type { BlogVariant } from "../../lib/types";
	import { blogStore } from "../../lib/stores/blogStore";
	import VariantCreator from "./@components/VariantInput/VariantCreator.svelte";

    function hasVariant(languageId: number) {
        return $blogStore.variants.some(v => v.language_id === languageId);
    }

    function getVariantValue(languageId: number, key: keyof BlogVariant) {
        const variant = $blogStore.variants.find(v => v.language_id === languageId);
        if (variant) {
            return variant[key];
        }
        return '';
    }


    function handleNameChange(e: any) {

    }

    function handleDescriptionChange(e: any) {

    }


</script>

<BlogSettingsSave 
    keys={['code_head', 'code_foot', 'flashload']}
/>

<div class="settings">

    <SplitControl
        label="Name"
        caption="Name of your blog"
    >

        <div slot="nested">
            {#each $languagesStore as language}
                <SplitControl
                    label={language.name}
                    caption={language.code}
                >
                    {#if hasVariant(language.id)}
                        <TextInput 
                            value={getVariantValue(language.id, 'name')}
                            on:change={handleNameChange}
                        />
                    {:else}
                        <VariantCreator {language} />
                    {/if}
                </SplitControl>
            {/each}
        </div>

    </SplitControl>

    <SplitControl
        label="Description"
        caption="A short description (or sub-title) for your blog"
    >

        <div slot="nested">
            {#each $languagesStore as language}
                <SplitControl
                    label={language.name}
                    caption={language.code}
                >
                    {#if hasVariant(language.id)}
                        <TextInput 
                            value={getVariantValue(language.id, 'description')}
                            on:change={handleDescriptionChange}
                        />
                    {:else}
                        <VariantCreator {language} />
                    {/if}
                </SplitControl>
            {/each}
        </div>

    </SplitControl>

</div>

<style>
    .settings {
        flex: 1;
        overflow: auto;
        padding: 25px 30px;
    }

    .settings :global(.CodeMirror) {
        min-height: 200px;
    }
    .intro {
        padding:15px;
    }
</style>