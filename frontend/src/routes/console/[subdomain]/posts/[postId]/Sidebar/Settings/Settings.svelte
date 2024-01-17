<script lang="ts">
	import { Button, Checkbox, SplitControl, TextInput, Textarea } from "@hyvor/design/components";
	import { IconTrash } from "@hyvor/icons";
	import { postStore } from "../../../postStore";
	import Slug from "./Slug.svelte";
	import Description from "./Description.svelte";
	import Authors from "./Authors/Authors.svelte";
	import Tags from "./Tags/Tags.svelte";
	import PublishTime from "./PublishTime.svelte";
	import Status from "./Status/Status.svelte";
	import CoverImage from "./CoverImage.svelte";
	import Featured from "./Featured.svelte";
	import Delete from "./Delete.svelte";

    let type: 'basic' | 'advanced' = 'basic';
</script>

<div class="settings-wrap">

    <Status />

    {#if type === 'basic'}

        <Slug />
        <Description />
        <Authors />
        <Tags />
        <CoverImage />
        <PublishTime />
        <Featured />
        <Delete />

    {:else}

        <SplitControl>
            <span slot="label">Canonical URL</span>
            <TextInput 
                block
                value={$postStore.canonical_url}
            />
        </SplitControl>

        <SplitControl>
            <span slot="label">Head Code</span>
            <Textarea
                block
                rows={4}
                value={$postStore.code_head || ''}
            />
        </SplitControl>

        <SplitControl>
            <span slot="label">Foot Code</span>
            <Textarea
                block
                rows={4}
                value={$postStore.code_foot || ''}
            />
        </SplitControl>

    {/if}


</div>


<style>

    .switch {
        text-align: center;
        margin-bottom: 10px;
    }

    .settings-wrap :global(.split-control > .left) {
        flex: 3;
        min-width: initial;
    }

    .settings-wrap :global(.split-control > .right) {
        flex: 7;
    }

</style>
