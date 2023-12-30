<script lang="ts">
	import { FormControl, Radio, SplitControl, Switch } from "@hyvor/design/components";
	import { blogStore, updateBlogStore } from "../../../lib/stores/blogStore";
	import CodemirrorEditor from "../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte";
	import BlogSettingsSave from "../BlogSettingsSave.svelte";

    function handleAllowIndexingChange(e: any) {
        updateBlogStore({ seo_indexing: e.target.checked });
    }

    function handleExternalLinksFollowChange(e: any, value: 'follow' | 'nofollow') {
        updateBlogStore({ seo_external_links_follow: value });
    }

</script>

<BlogSettingsSave 
    keys={['seo_indexing', 'seo_external_links_follow', 'seo_robots_txt']}
/>

<div class="settings">
        
    <SplitControl
        label="Allow indexing"
        caption="Allow search engines to index your blog"
    >

        <Switch
            checked={$blogStore.seo_indexing}
            on:change={handleAllowIndexingChange}
        />

    </SplitControl>

    <SplitControl
        label="External links type"
        caption="Should search engines follow external links?"
    >

        <FormControl>

            <Radio 
                value="follow" 
                group={$blogStore.seo_external_links_follow}
                on:change={e => handleExternalLinksFollowChange(e, 'follow')}
            >
                Follow
            </Radio>

            <Radio 
                value="nofollow" 
                group={$blogStore.seo_external_links_follow}
                on:change={e => handleExternalLinksFollowChange(e, 'nofollow')}
            >
                No follow
            </Radio>

        </FormControl>

    </SplitControl>

    <div class="robots-split">
        <SplitControl
            label="Robots.txt"
            caption="Customize your robots.txt file"
        >

            <CodemirrorEditor 
                value={$blogStore.seo_robots_txt || ''}
                id="robots_txt"
                ext="twig"
                on:change={e => updateBlogStore({ seo_robots_txt: e.detail })}
            />

        </SplitControl>
    </div>

</div>

<style>
    .robots-split :global(.split-control) {
        flex-direction: column;
    }
    .robots-split :global(.CodeMirror) {
        min-height: 150px;
    }
    .settings {
        flex: 1;
        overflow: auto;
        padding: 25px 30px;
    }
</style>