<script lang="ts">
    import { IconButton, Modal, TextInput, ColorPicker, FormControl } from "@hyvor/design/components";
    import { IconPencil, IconTrash, IconLink45deg, IconArrowsAngleContract, IconArrowsAngleExpand, IconDash, IconAlignStart, IconAlignMiddle, IconAlignEnd } from '@hyvor/icons';

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

    const handleSizeChange = (newSize: string) => {
        changeAttr('size', newSize);
    }

    const handleAlignChange = (newAlign: string) => {
        changeAttr('align', newAlign);
    }

    function handleBgChange(e: CustomEvent<string>) {
        changeAttr('bg', e.detail)
    }

    function handleFgChange(e: CustomEvent<string>) {
        changeAttr('fg', e.detail)
    }

</script>

<div class="button-editor">
    {#if showLinkInput}
        <Modal 
            bind:show={showLinkInput}
            size="medium"
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
            <FormControl>
                <TextInput bind:value={href} placeholder="Link" />
            </FormControl>

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

            <IconButton size="small" color="accent" on:click={() => handleSizeChange('small')}>
                <IconArrowsAngleContract size={14} />
            </IconButton>
            <IconButton size="small" color="accent" on:click={() => handleSizeChange('medium')}>
                <IconDash size={14} />
            </IconButton>
            <IconButton size="small" color="accent" on:click={() => handleSizeChange('large')}>
                <IconArrowsAngleExpand size={14} />
            </IconButton>

            <IconButton size="small" color="accent" on:click={() => handleAlignChange('left')}>
                <IconAlignStart size={14} />
            </IconButton>
            <IconButton size="small" color="accent" on:click={() => handleAlignChange('center')}>
                <IconAlignMiddle size={14} />
            </IconButton>

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

            <IconButton size="small" color="accent" on:click={() => handleAlignChange('right')}>
                <IconAlignEnd size={14} />
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
