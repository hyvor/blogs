<script lang="ts">
    import { IconButton, Modal, TextInput, ColorPicker, FormControl, Tooltip } from "@hyvor/design/components";
    import { IconTrash, IconLink45deg, IconArrowsAngleContract, IconArrowsAngleExpand, IconDash, IconTextLeft, IconTextCenter, IconTextRight, IconSquareHalf, IconSquare } from '@hyvor/icons';
	import { onMount } from "svelte";

    export let showEditMenu = false;
    export let href: string;
    export let align: string;
    export let size: string;
    export let bg: string;
    export let fg: string;
    export let changeAttr: (name: string, value: string) => void;
    export let deleteNode: () => void;

    const handleLinkChange = (e: Event) => {
        const new_href = (e.target as HTMLInputElement).value;
        changeAttr('href', new_href);
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
    
    {#if showEditMenu}
        <div class="button-editor-menu" style={align === 'left' ? "left: 0%" : align === 'center' ? "left: 25%" : "right: 0%"}>
            <div class="link-editor">
                <FormControl>
                    <TextInput 
                        label="Link"
                        value={href}
                        on:input={e => handleLinkChange(e)}
                    />
                </FormControl>
            </div>
            <div class="button-editor-buttons">
                <Tooltip text="Size small">
                    <IconButton size="small" color='input' variant={size == 'small' ? 'fill' :'invisible'} on:click={() => handleSizeChange('small')}>
                        <IconSquare size={8} />
                    </IconButton>
                </Tooltip>
                <Tooltip text="Size medium">
                    <IconButton size="small" color='input'  variant={size == 'medium' ? 'fill' :'invisible'} on:click={() => handleSizeChange('medium')}>
                        <IconSquare size={11} />
                    </IconButton>
                </Tooltip>
                <Tooltip text="Size large">
                    <IconButton size="small" color='input' variant={size == 'large' ? 'fill' :'invisible'}  on:click={() => handleSizeChange('large')}>
                        <IconSquare size={14} />
                    </IconButton>
                </Tooltip>

                <div class="separator"></div>

                <Tooltip text="Align start">
                    <IconButton size="small" color='input' variant={align == 'left' ? 'fill' :'invisible'}  on:click={() => handleAlignChange('left')}>
                        <IconTextLeft size={14} />
                    </IconButton>
                </Tooltip>
                <Tooltip text="Align center">
                    <IconButton size="small" color='input' variant={align == 'center' ? 'fill' :'invisible'}  on:click={() => handleAlignChange('center')}>
                        <IconTextCenter size={14} />
                    </IconButton>
                </Tooltip>
                <Tooltip text="Align end">
                    <IconButton size="small" color='input' variant={align == 'right' ? 'fill' :'invisible'}  on:click={() => handleAlignChange('right')}>
                        <IconTextRight size={14} />
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
                    <IconButton size="small" color="input" on:click={deleteNode} variant='invisible'>
                        <IconTrash size={14} />
                    </IconButton>
                </Tooltip>
            </div>
        </div>
    {/if}
</div>

<style>
    .button-editor-menu {
        position: absolute;
        top: -70px;
        background-color: var(--gray-light);
        padding: 4px;
        border-radius: 20px;   
    }

    .button-editor-buttons {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 2px;
    }

    .separator {
        height: 25px;
        width: 1px;
        background-color: var(--gray); 
        margin: 0 6px;
    }

    .link-editor {
        height: 10px;
        color: var(--link);
        text-decoration: underline;
    }
</style>
