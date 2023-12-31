<script lang="ts">
	import { Loader } from "@hyvor/design/components";
	import type { GptPrompt } from "../../../../../../lib/types";

    export let isLoading = true;

    let prompts : GptPrompt[] = [];
    let pendingPrompt: string | null = null;

</script>

<div class="ai-chat">

    <div class="chat-display">

        {#if isLoading}

            <div class="loader-wrap">
                <Loader padding={100} />
            </div>

        {:else}

            {#if !prompts.length && !pendingPrompt}

                

            {:else}

            {/if}

        {/if}

    </div>

</div>


<style>

    .ai-chat {
        height: 100%;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .chat-display {
        flex: 1;
        overflow: auto;
    }

    .loader-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

</style>