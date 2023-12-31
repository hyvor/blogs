<script lang="ts">
	import { ActionList, ActionListItem, Button, Dropdown } from "@hyvor/design/components";
	import { IconBoxArrowUpRight } from "@hyvor/icons";
	import { blogStore } from "../../../../../lib/stores/blogStore";
	import { postLanguageStore, postStore, postVariantStore } from "../../../postStore";

    let showDropdown = false;

    function handleOpenNewTab(url: string) {
        window.open(url, '_blank');
    }

    $: previewUrl = $blogStore.url +
        '/p/' + $postStore.preview_id +
        "/" + $postLanguageStore.code;

    function handleClick(e: MouseEvent) {

        e.stopPropagation();

        if ($postVariantStore.status === 'published') {
            showDropdown = true;
        } else {
            handleOpenNewTab(previewUrl);
        }

    }

</script>

<Dropdown align="center" bind:show={showDropdown} width={200}>

    <Button 
        size="medium" 
        color="light" 
        slot="trigger"
        on:click={handleClick}
    >
        { $postVariantStore.status === 'published' ? 'View' : 'Preview' }
        <IconBoxArrowUpRight slot="end" size={14} />
    </Button>

    <div class="dropdown-content" slot="content">

        <Button 
            block 
            color="light"
            on:click={() => handleOpenNewTab(previewUrl)}
        >
            Preview
            <IconBoxArrowUpRight slot="end" size={14} />
        </Button>

        <Button 
            block
            on:click={() => handleOpenNewTab($blogStore.url)}
        >
            Published Post
            <IconBoxArrowUpRight slot="end" size={14} />
        </Button>

    </div>

</Dropdown>

<style>

    .dropdown-content :global(button:nth-child(2)) {
        margin-top: 8px;
        background-color: var(--green-light)!important;
        color: var(--green-dark)!important;
    }

</style>