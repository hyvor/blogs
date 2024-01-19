<script lang="ts">
	import { Button, Divider, Loader, Textarea, Tooltip, toast } from "@hyvor/design/components";
	import type { GptPrompt } from "../../../../../lib/types";
	import { blogStore } from "../../../../../lib/stores/blogStore";

    export let gptPrompt: GptPrompt;

    function getResponseHtml(response: string | null) {
    
}

    let imageUrl = $blogStore.icon_url || $blogStore.icon_url;
</script>

<div class="single-prompt">
    <div class="prompt">
        {#if imageUrl}
            <img src={imageUrl} />
        {:else}
            <span class="blog-logo">{$blogStore.subdomain[0]?.toUpperCase()}</span>
        {/if}
        <div class="prompt-text">
            {gptPrompt.prompt}
        </div>
    </div>
    <div class="response">
        <span class="blog-logo">{"H"}</span>
        {#if gptPrompt.gpt_response}
            {@html gptPrompt.gpt_response}
        {:else}
            <div class="response-text">
                <Loader />
            </div>
        {/if}
    </div>

</div>

<style>
    .prompt {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .prompt-text {
        margin-left: 10px;
    }

    .blog-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: var(--accent-light);
    }
</style>