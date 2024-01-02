<script lang="ts">
	import { Button, TextInput, Validation } from "@hyvor/design/components";
	import { IconArrowReturnLeft } from "@hyvor/icons";
	import { createEventDispatcher } from "svelte";

    let input = '';

    function isRelative(val: string) {
        // does not have protocol
        return !/^[a-zA-Z0-9]+:\/\//.test(val);
    }

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

{#if input.trim() !== '' &&  isRelative(input)}
    <div class="warning-wrap">
        <Validation state="warning">
            You are adding a <strong>relative link</strong>.
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

<style>
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