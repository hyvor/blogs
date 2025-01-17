<script lang="ts">
	import { Button, TextInput, Validation } from "@hyvor/design/components";
	import { IconArrowReturnLeft } from "@hyvor/icons";
	import { createEventDispatcher } from "svelte";
	import { getHeadingsFromContent } from "../../../../../../../../../lib/prosemirror/helpers";
	import { postCurrentContentStore } from "../../../../../../postStore";

    export let input = '';

    $: isRelative = !/^[a-zA-Z0-9]+:\/\//.test(input);
    $: isAnchor = /^#/.test(input);
    $: headings = getHeadingsFromContent($postCurrentContentStore);
    $: isAnchorAvailable = headings.find((heading) => heading.id === input.replace('#', ''));

    const dispatch = createEventDispatcher();

    function handleClick() {
        dispatch('add', input.trim());
    }

    function handleKeyup(e: KeyboardEvent) {
        if (e.key === 'Enter') {
            handleClick();
        }
    }
</script>

<TextInput
    placeholder="Paste a link..."
    block
    autofocus
    bind:value={input}

    on:keyup={handleKeyup}
/>

{#if input.trim() !== '' &&  (isRelative || isAnchor)}
    <div class="warning-wrap">
        <Validation 
            state={
                isAnchor ?
                    (isAnchorAvailable ? 'success' : 'error') :
                    'warning'
            }
        >
            {#if isAnchor && !isAnchorAvailable}
                You are adding a <strong>missing anchor link</strong>.
            {:else if isAnchor && isAnchorAvailable}
                You are adding an <strong>anchor link</strong>.
            {:else if isRelative}
                You are adding a <strong>relative link</strong>.
            {/if}
        </Validation>
    </div>
{/if}

<div class="buttons">

    {#if input.trim().length !== 0}
        <span class="enter">
            Press Enter
        </span>
    {/if}

    <Button
        variant="fill"
        disabled={input.trim().length === 0}
        on:click={handleClick}
    >
        Add Link <IconArrowReturnLeft slot="end" size={12} />
    </Button>
</div>

<style lang="scss">

    .buttons {
        margin-top: 15px;
        text-align: right;
        margin-bottom: 15px;
    }
    .warning-wrap {
        margin-top: 10px;
    }
    .enter {
        font-size: 12px;
        margin-right: 5px;
        color: var(--text-light);
    }
</style>