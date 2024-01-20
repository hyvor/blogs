<script lang="ts">
	import { Button, SplitControl, TextInput, Textarea } from "@hyvor/design/components";
	import { postLanguageStore, postStore } from "../../../postStore";
	import Slug from "./Slug.svelte";
	import Description from "./Description.svelte";
	import Authors from "./Authors/Authors.svelte";
	import Tags from "./Tags/Tags.svelte";
	import PublishTime from "./PublishTime.svelte";
	import Status from "./Status/Status.svelte";
	import CoverImage from "./CoverImage.svelte";
	import Featured from "./Featured.svelte";
	import Delete from "./Delete.svelte";
	import { IconCaretDown, IconCaretRight } from "@hyvor/icons";
	import CanonicalUrl from "./CanonicalUrl.svelte";

    let showAdvanced = false;
</script>

<div class="settings-wrap">

    <Status />
    <Slug />
    <Description />
    <Authors />
    <Tags />
    <CoverImage />
    <PublishTime />
    <Featured />
    <Delete />

    {#if $postLanguageStore.is_primary}
        <div class="advanced-wrap">
            <Button 
                color="input" 
                size="small"
                on:click={() => showAdvanced = !showAdvanced}
            >
                <svelte:component 
                    this={showAdvanced ? IconCaretDown : IconCaretRight} 
                    size={12} 
                    slot="end" 
                />
            </Button>
        </div>
    {/if}

    {#if showAdvanced}

        <CanonicalUrl />

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

    .settings-wrap :global(.split-control > .left) {
        flex: 3;
        min-width: initial;
    }

    .settings-wrap :global(.split-control > .right) {
        flex: 7;
    }

    .advanced-wrap {
        padding: 10px 10px;
    }

</style>
