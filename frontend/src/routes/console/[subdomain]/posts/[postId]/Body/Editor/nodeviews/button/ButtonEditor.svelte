<script lang="ts">
    import { TextInput } from '@hyvor/design/components';
    import type { EditorView } from 'prosemirror-view';
    import { IconButton } from "@hyvor/design/components";
    import { IconPencil } from '@hyvor/icons';

    export let href: string;
    export let align: string;
    export let size: string;
    export let bg: string;
    export let fg: string;
    export let changeAttr: (name: string, value: string) => void;

    let showEditMenu = false;
    console.log('Menu component:', showEditMenu);

    const toggleEditMenu = () => {
        showEditMenu = !showEditMenu;
        console.log('Menu component:', showEditMenu);
    };

</script>

<div class="button-editor">
    {#if !showEditMenu}
        <div class="menu-button">
            <IconButton size="small" color="input" on:click={toggleEditMenu}>
                <IconPencil size={14} />
            </IconButton>
        </div>
    {:else}
        <div class="menu">
            <TextInput label="Link" bind:value={href} />
            <TextInput label="Align" bind:value={align} />
            <TextInput label="Size" bind:value={size} />
            <TextInput label="Background" bind:value={bg} />
            <TextInput label="Foreground" bind:value={fg} />
            <IconButton size="small" color="input" on:click={toggleEditMenu}>
                <IconPencil size={14} />
            </IconButton>
        </div>
    {/if}
</div>

<style>
    .menu-button {
        float: right;
    }
</style>
