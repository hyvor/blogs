<script lang="ts">
    import { TextInput } from '@hyvor/design/components';
    import type { EditorView } from 'prosemirror-view';
    import { IconButton, Modal } from "@hyvor/design/components";
    import { IconPencil, IconTrash, IconLink45deg } from '@hyvor/icons';

    export let href: string;
    export let align: string;
    export let size: string;
    export let bg: string;
    export let fg: string;
    export let changeAttr: (name: string, value: string) => void;
    export let deleteNode: () => void;

    let showEditMenu = false;
    let showLinkInput = false;

    const toggleEditMenu = () => {
        showEditMenu = !showEditMenu;
    };

    const toggleLinkInput = () => {
        showLinkInput = !showLinkInput;
    };

    const handleLinkChange = () => {
        changeAttr('href', href);
        toggleLinkInput();
    }

</script>

<div class="button-editor">
    {#if showLinkInput}
        <Modal 
            bind:show={showLinkInput}
            closeOnOutsideClick={true} 
            closeOnEscape={true}
            title="Edit button link"
            footer={{
                cancel: {
                    text: 'Cancel',
                },
                confirm: {
                    text: 'Change',
                    danger: true
                }
            }}
             on:cancel={toggleLinkInput}
             on:confirm={handleLinkChange}
        >
            <TextInput bind:value={href} placeholder="Link" />

        </Modal>
    {/if}
    {#if !showEditMenu}
        <div class="menu-button">
            <IconButton size="small" color="input" on:click={toggleEditMenu}>
                <IconPencil size={14} />
            </IconButton>
        </div>
    {:else}
        <div class="button-editor-menu">
            <IconButton size="small" color="accent" on:click={toggleLinkInput}>
                <IconLink45deg size={14} />
            </IconButton>
            <IconButton size="small" color="accent" on:click={deleteNode}>
                <IconTrash size={14} />
            </IconButton>
        </div>
    {/if}
</div>

<style>
    .menu-button {
        float: right;
    }
</style>
