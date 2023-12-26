<script lang="ts">
	import { Avatar, IconButton, SplitControl, Tag } from "@hyvor/design/components";
    import { postStore } from "../../../../../../../lib/stores/postStore";
	import type { Tag as TagType } from "../../../../../../../lib/types";
	import { getPrimaryLanguage } from "../../../../../../../lib/stores/languagesStore";
	import { IconPlus, IconX } from "@hyvor/icons";

    function getTagName(tag: TagType) {
        const primaryLang = getPrimaryLanguage()
        const variant = tag.variants.find(v => v.language_id === primaryLang.id);
        return variant?.name || 'Unknown tag';
    }

    function handleRemoveTag(authorId: number) {
        // TODO: implement
    }

</script>


<SplitControl>
    <span slot="label">Tags</span>

    <div class="tags">

        <div class="left">

            {#each $postStore.tags as tag}
                <Tag size="small" bg="#f1f1f1">
                    { getTagName(tag) }

                    <IconButton 
                        color="danger" 
                        on:click={() => handleRemoveTag(tag.id)}
                        size={16}
                        slot="end"
                    >
                        <IconX size={12} />
                    </IconButton>
                </Tag>
            {/each}

        </div>

        <div class="right">

            <IconButton
                color="soft"
                size="small"
            >
                <IconPlus size={16} />
            </IconButton>

        </div>

    </div>

    
</SplitControl>

<style>
    .tags {
        display: flex;
    }

    .left {
        flex: 1;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
    }

    .right {
        
    }

</style>