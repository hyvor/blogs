<script lang="ts">
	import { postVariantStore } from "../../postStore";
	import PostStatusTag from "../../PostStatusTag.svelte";
    import LanguageSelector from "./LanguageSelector/LanguageSelector.svelte";
	import PreviewButton from "../Sidebar/Top/PreviewButton.svelte";
	import UnpublishButton from "../Sidebar/Settings/Status/UnpublishButton.svelte";
	import PublishButton from "../Sidebar/Top/PublishButton.svelte";
	import UpdateButton from "../Sidebar/Top/UpdateButton.svelte";
	import { Z_INDEX, increaseZIndex } from "../z-index";

    let div: HTMLDivElement;

    function handleClick() {
        div.style.zIndex = Z_INDEX + 1 + "";
        increaseZIndex();
    }
</script>


<!-- svelte-ignore a11y-click-events-have-key-events -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<div 
    class="post-header hds-box"
    on:click={handleClick}
    bind:this={div}
>

    <div style="display:flex;align-items:center;">

        <div style="display:flex;flex:1;">
            <LanguageSelector />
        </div>

        <div style="display:flex;gap:6px;align-items:center;">

            <PostStatusTag 
                status={$postVariantStore.status} 
                size="small" 
            />
            
            <PreviewButton />
            <UnpublishButton />
            <PublishButton />
            <UpdateButton />

        </div>

    </div>

</div>


<style>

    .post-header {
        padding: 15px 25px;
        position: sticky;
        top: -20px;
        z-index: 10;
    }
    
</style>