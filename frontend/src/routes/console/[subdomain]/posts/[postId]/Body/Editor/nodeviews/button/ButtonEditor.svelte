<script lang="ts">
    import { IconButton, Modal, TextInput, ColorPicker, FormControl, Tooltip } from "@hyvor/design/components";
    import { IconPencil, IconTrash, IconLink45deg, IconArrowsAngleContract, IconArrowsAngleExpand, IconDash, IconAlignStart, IconAlignMiddle, IconAlignEnd } from '@hyvor/icons';

    export let showEditMenu = false;
    export let href: string;
    export let align: string;
    export let size: string;
    export let bg: string;
    export let fg: string;
    export let changeAttr: (name: string, value: string) => void;
    export let deleteNode: () => void;

    let showLinkInput = false;

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
             on:cancel={() => showLinkInput = false}
             on:confirm={handleLinkChange}
        >
        {#if href}
            <div class="current-link">Current link: {href}</div>
        {:else}
                <div class="current-link">No link set</div>
        {/if}

            <FormControl>
                <TextInput bind:value={href} placeholder="Link" />
            </FormControl>

        </Modal>
    {/if}
    {#if showEditMenu}
        <div class="button-editor-menu">
            
            <Tooltip text="Change button link">
                <IconButton size="small" color="input" on:click={toggleLinkInput}>
                    <IconLink45deg size={14} />
                </IconButton>
            </Tooltip>
            
            <div class="separator"></div>

            <Tooltip text="Size small">
                <IconButton size="small" color={size == 'small' ? 'accent' : 'input'} on:click={() => handleSizeChange('small')}>
                    <IconArrowsAngleContract size={14} />
                </IconButton>
            </Tooltip>
            <Tooltip text="Size medium">
                <IconButton size="small" color={size == 'medium' ? 'accent' : 'input'} on:click={() => handleSizeChange('medium')}>
                    <IconDash size={14} />
                </IconButton>
            </Tooltip>
            <Tooltip text="Size large">
                <IconButton size="small" color={size == 'large' ? 'accent' : 'input'} on:click={() => handleSizeChange('large')}>
                    <IconArrowsAngleExpand size={14} />
                </IconButton>
            </Tooltip>

            <div class="separator"></div>

            <Tooltip text="Align start">
                <IconButton size="small" color={align == 'left' ? 'accent' : 'input'} on:click={() => handleAlignChange('left')}>
                    <IconAlignStart size={14} />
                </IconButton>
            </Tooltip>
            <Tooltip text="Align center">
                <IconButton size="small" color={align == 'center' ? 'accent' : 'input'} on:click={() => handleAlignChange('center')}>
                    <IconAlignMiddle size={14} />
                </IconButton>
            </Tooltip>
            <Tooltip text="Align end">
                <IconButton size="small" color={align == 'right' ? 'accent' : 'input'} on:click={() => handleAlignChange('right')}>
                    <IconAlignEnd size={14} />
                </IconButton>
            </Tooltip>

            <div class="separator"></div>

            <Tooltip text="Change background color">
                <ColorPicker 
                    size={20}
                    color={bg}
                    on:input={handleBgChange}
                />
            </Tooltip>
            <Tooltip text="Change foreground color">
                <ColorPicker 
                    size={20}
                    color={fg}
                    on:input={handleFgChange}
                />
            </Tooltip>

            <div class="separator"></div>
           
            <Tooltip text="Remove button">
                <IconButton size="small" color="input" on:click={deleteNode}>
                <IconTrash size={14} />
            </IconButton>
            </Tooltip>

        </div>
    {/if}
</div>

<style>
    .button-editor-menu {
        position: absolute;
        top: -15px;
        right: 0;
        background-color: var(--gray-light);
        padding: 4px;
        border-radius: 20px;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 2px;
    }
    .current-link {
        margin-bottom: 10px;
        font-size: 12px;
    }
    .separator {
        height: 25px;
        width: 1px;
        background-color: var(--gray); 
        margin: 0 6px;
    }
</style>
