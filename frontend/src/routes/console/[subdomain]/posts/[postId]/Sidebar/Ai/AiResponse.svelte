<script lang="ts">
	import { Button, Divider, Loader, Textarea, Tooltip, toast } from "@hyvor/design/components";
	import type { GptPrompt } from "../../../../../lib/types";
	import { blogStore } from "../../../../../lib/stores/blogStore";
	import { IconClipboard, IconCopy, IconFileEarmark } from "@hyvor/icons";
    import hyvorLogo from './logo.png';

    export let gptPrompt: GptPrompt;
    export let loading: boolean;

    function getResponseHtml(response: string | null) {
        // TODO
    }

    // Copy the response to clipboard
    async function handleCopy() {
        try {
            await navigator.clipboard.writeText(gptPrompt.gpt_response);
            toast.success("Copied to clipboard");
        } catch (err) {
            toast.error("Failed to copy to clipboard");
        }
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
    <div class="prompt-response">
        <span class="blog-logo"><img src={hyvorLogo} width="20px"/></span>
        {#if gptPrompt.gpt_response && !loading}
            <span class="prompt-response">{@html gptPrompt.gpt_response}</span>
            <div class="prompt-response-button-row">
                <Button color="gray" on:click={() => handleCopy()}>
                    <IconClipboard />
                    <span class="prompt-response-button-content">Copy</span>
                </Button>
                <Button color="gray">
                    <IconFileEarmark />
                    <span class="prompt-response-button-content">Add to Editor</span>
                </Button>
            </div>
        {:else}
            <span class="prompt-loader"><Loader /></span>
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

    .prompt-response {
        margin-top: 10px;
    }

    .prompt-response-button-row {
        text-align: right;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .prompt-response-button-content{
        margin-left: 5px;
        font-size: 10px;
    }

    .prompt-loader {
        margin-left: 10px;
    }

</style>