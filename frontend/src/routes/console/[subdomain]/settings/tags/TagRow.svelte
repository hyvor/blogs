<script lang="ts">
	import { IconButton, Link, TableRow, Tooltip } from "@hyvor/design/components";
	import type { Tag } from "../../../lib/types";
	import { primaryLanguageStore } from "../../../lib/stores/languagesStore";
	import { IconPencilFill, IconTrash } from "@hyvor/icons";

    export let tag: Tag;

    $: variant = tag.variants.find(v => v.language_id === $primaryLanguageStore.id);

    let isEditing = false;

    function handleDelete() {

    }

</script>

<TableRow>
    <div>{variant?.name || 'Unnamed'}</div>
    <div>
        <Link
            href={variant?.url || ''}
            target="_blank"
        >
            {tag.slug}
        </Link>
    </div>
    <div>{variant?.description || ''}</div>
    <div>{tag.posts_count}</div>
    <div>
        <Tooltip text="Edit tag">
            <IconButton 
                variant="fill-light" 
                color="gray" 
                size="small"
                on:click={() => isEditing = true}
            >
                <IconPencilFill size={12} />
            </IconButton>
        </Tooltip>
        <Tooltip text="Delete tag">  
            <IconButton 
                variant="fill-light" 
                color="red" 
                size="small"
                on:click={handleDelete}
            >
                <IconTrash size={12} />
            </IconButton>
        </Tooltip>
    </div>
</TableRow>