<script lang="ts">
	import { ColorPicker } from "@hyvor/design/components";

    export let bg: string;
    export let fg: string;
    export let changeAttr: (name: string, value: string) => void;

    function handleBgChange(e: CustomEvent<string>) {
        changeAttr('bg', e.detail)
    }

    function handleFgChange(e: CustomEvent<string>) {
        changeAttr('fg', e.detail)
    }

</script>

<div contenteditable="false">
    <ColorPicker 
        size={20}
        color={bg}
        on:input={handleBgChange}
    />
    <ColorPicker 
        size={20}
        color={fg}
        on:input={handleFgChange}
    />
</div>

<style>
    div :global(div) {
        z-index: 1000;
    }
    div {
        display: flex;
        align-items: center;
    }
</style>