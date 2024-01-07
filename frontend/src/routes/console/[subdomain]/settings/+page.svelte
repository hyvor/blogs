<script lang="ts">
	import { SplitControl, TextInput } from "@hyvor/design/components";
	import BlogSettingsSave from "./BlogSettingsSave.svelte";
	import type { Blog, BlogVariant } from "../../lib/types";
	import { blogStore, updateBlogStoreVariantValue } from "../../lib/stores/blogStore";
	import VariantInput from "./@components/VariantInput/VariantInput.svelte";


    function handleNameChange(e: CustomEvent<{languageId: number, value: string}>) {
        updateBlogStoreVariantValue(e.detail.languageId, 'name', e.detail.value);
    }

    function handleDescriptionChange(e: CustomEvent<{languageId: number, value: string}>) {
        updateBlogStoreVariantValue(e.detail.languageId, 'description', e.detail.value);
    }

    function handleBlogValueChangeEvent(e: any, key: keyof Blog) {
        blogStore.update(b => {
            return {
                ...b, 
                [key]: e.target.value
            }
        });
    }

</script>

<BlogSettingsSave 
    keys={[
        'social_facebook',
        'social_twitter',
        'social_linkedin',
        'social_youtube',
        'social_tiktok',
        'social_instagram',
        'social_github',
    ]}
    variantKeys={[
        'name',
        'description',
    ]}
/>

<div class="settings">

    <VariantInput 
        label="Name"
        caption="Name of your blog"
        type="blog"
        obj={$blogStore}
        key="name"
        maxlength={160}
        on:change={handleNameChange}
    />

    <VariantInput 
        label="Description"
        caption="A short description (or sub-title) for your blog"
        type="blog"
        obj={$blogStore}
        key="description"
        maxlength={255}
        on:change={handleDescriptionChange}
    />

    <SplitControl
        label="Social Media"
        caption="Links to your social media channels (use full URLs with https://)"
    >

        <div slot="nested">

            <SplitControl
                label="Facebook"
            >
                <TextInput 
                    block
                    value={$blogStore.social_facebook}
                    on:input={e => handleBlogValueChangeEvent(e, 'social_facebook')}
                />
            </SplitControl>

            <SplitControl
                label="Twitter"
            >
                <TextInput 
                    block
                    value={$blogStore.social_twitter}
                    on:input={e => handleBlogValueChangeEvent(e, 'social_twitter')}
                />
            </SplitControl>

            <!-- Linkedin -->
            <SplitControl
                label="Linkedin"
            >
                <TextInput 
                    block
                    value={$blogStore.social_linkedin}
                    on:input={e => handleBlogValueChangeEvent(e, 'social_linkedin')}
                />
            </SplitControl>

            <!-- Youtube -->
            <SplitControl
                label="Youtube"
            >
                <TextInput 
                    block
                    value={$blogStore.social_youtube}
                    on:input={e => handleBlogValueChangeEvent(e, 'social_youtube')}
                />
            </SplitControl>

            <!-- TikTok -->
            <SplitControl
                label="TikTok"
            >
                <TextInput 
                    block
                    value={$blogStore.social_tiktok}
                    on:input={e => handleBlogValueChangeEvent(e, 'social_tiktok')}
                />
            </SplitControl>
           
            <!-- Instagram -->
            <SplitControl
                label="Instagram"
            >
                <TextInput 
                    block
                    value={$blogStore.social_instagram}
                    on:input={e => handleBlogValueChangeEvent(e, 'social_instagram')}
                />
            </SplitControl>

            <!-- Github -->
            <SplitControl
                label="Github"
            >
                <TextInput 
                    block
                    value={$blogStore.social_github}
                    on:input={e => handleBlogValueChangeEvent(e, 'social_github')}
                />
            </SplitControl>

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