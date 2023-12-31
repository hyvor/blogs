<script lang="ts">
	import { Avatar, Dropdown, IconButton, SplitControl, Tag, TextInput } from "@hyvor/design/components";
    import { postStore } from "../../../../postStore";
	import type { User } from "../../../../../../lib/types";
	import { getPrimaryLanguage } from "../../../../../../lib/stores/languagesStore";
	import { IconPlus, IconX } from "@hyvor/icons";
	import AuthorsSearch from "./AuthorsSearch.svelte";

    let dropdownOpen = false;

    function getAuthorName(user: User) {
        const primaryLang = getPrimaryLanguage()
        const variant = user.variants.find(v => v.language_id === primaryLang.id);
        return variant?.name || 'Unknown user';
    }

    function handleRemoveAuthor(authorId: number) {
        // TODO: implement
    }

    function handleAddAuthor(user: User) {
        dropdownOpen = false;
    }

</script>

<SplitControl>
    <span slot="label">Authors</span>

    <div class="authors">

        <div class="left">

            {#each $postStore.authors as author}
                <Tag size="small" style="padding: 4px 8px" bg="#f1f1f1">
                    <Avatar 
                        src={author.picture_url} 
                        alt={getAuthorName(author)} 
                        slot="start"
                        size={16}
                    />
                    { getAuthorName(author) }

                    <IconButton 
                        color="danger" 
                        on:click={() => handleRemoveAuthor(author.id)}
                        size={16}
                        slot="end"
                    >
                        <IconX size={12} />
                    </IconButton>
                </Tag>
            {/each}

        </div>

        <div class="right">

            <Dropdown
                position="bottom"
                align="end"
                width={300}
                bind:show={dropdownOpen}
            >
                <IconButton
                    color="soft"
                    size="small"
                    slot="trigger"
                >
                    <IconPlus size={16} />
                </IconButton>

                <AuthorsSearch 
                    slot="content"
                    on:select={handleAddAuthor}
                />
            
            </Dropdown>

        </div>

    </div>


</SplitControl>

<style>
    .authors {
        display: flex;
    }

    .left {
        flex: 1;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        min-width: 0;
    }

    .right {
        
    }

</style>