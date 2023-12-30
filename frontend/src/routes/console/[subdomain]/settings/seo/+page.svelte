<script lang="ts">
	import { FormControl, Radio, SplitControl, Switch } from "@hyvor/design/components";
	import { blogStore, updateBlogStore } from "../../../lib/stores/blogStore";
	import CodemirrorEditor from "../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte";


    function handleAllowIndexingChange(e: any) {
        updateBlogStore({ seo_indexing: e.target.checked });
    }

</script>


<SplitControl
    label="Allow indexing"
    caption="Allow search engines to index your blog"
>

    <Switch
        value={$blogStore.seo_indexing}
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
        >
            Follow
        </Radio>

        <Radio 
            value="nofollow" 
            group={$blogStore.seo_external_links_follow}
        >
            No follow
        </Radio>

    </FormControl>

</SplitControl>

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