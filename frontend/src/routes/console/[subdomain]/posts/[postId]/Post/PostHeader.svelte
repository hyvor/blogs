<script>
	import { postVariantStore } from "../../../../lib/stores/postStore";
	import PostStatusTag from "../../PostStatusTag.svelte";
    import LanguageSelector from "./LanguageSelector/LanguageSelector.svelte";
	import PreviewButton from "./Buttons/PreviewButton.svelte";
	import UnpublishButton from "./Buttons/UnpublishButton.svelte";
	import PublishButton from "./Buttons/PublishButton.svelte";
</script>


<div class="post-header hds-box">

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