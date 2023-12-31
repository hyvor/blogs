<script lang="ts">
	import { Button, ButtonGroup, Checkbox, SplitControl, TextInput, Textarea } from "@hyvor/design/components";
	import { IconTrash } from "@hyvor/icons";
	import { postStore, postVariantStore, updatePostVariantStore } from "../../../postStore";
	import Slug from "./Slug.svelte";
	import Description from "./Description.svelte";
	import Authors from "./Authors/Authors.svelte";
	import Tags from "./Tags/Tags.svelte";

    let type: 'basic' | 'advanced' = 'basic';

</script>

<div class="settings-wrap">

    <div class="switch">
        <ButtonGroup>
            <Button 
                size="x-small" 
                color={type === 'basic' ? 'accent' : 'invisible'}
                on:click={() => type = 'basic'}
            >Basic</Button>
            <Button 
                size="x-small" 
                color={type === 'advanced' ? 'accent' : 'invisible'}
                on:click={() => type = 'advanced'}
            >Advanced</Button>
        </ButtonGroup>
    </div>

    {#if type === 'basic'}

        <Slug />
        <Description />
        <Authors />
        <Tags />

        <SplitControl>
            <span slot="label">Cover Image</span>
            
        </SplitControl>

        <SplitControl>
            <span slot="label">Publish Time</span>
            <TextInput 
                block 
                type="datetime-local" 
            />
        </SplitControl>

        <SplitControl>
            <span slot="label">Featured</span>
            <Checkbox />
        </SplitControl>

        <SplitControl>
            <span slot="label">Delete Post</span>
            <Button color="danger" size="small">
                Delete
                <IconTrash slot="start" />
            </Button>
        </SplitControl>

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
