<script lang="ts">
	import { Avatar, Dropdown, IconButton, Loader, SplitControl, Tag, Text, TextInput } from "@hyvor/design/components";
    import { postOriginalStore, postStore, postVariantStore, updatePostStore } from "../../../../postStore";
	import type { User } from "../../../../../../../lib/types";
	import { getPrimaryLanguage } from "../../../../../../../lib/stores/languagesStore";
	import { IconPlus, IconX } from "@hyvor/icons";
	import AuthorsSearch from "./AuthorsSearch.svelte";
	import UnsavedTag from "../UnsavedTag.svelte";
	import { updatePost, updatePostAuthors } from "../../../../postActions";
	import OnlyPrimaryVariant from "../OnlyPrimaryVariant.svelte";
	import { hasIdArrayChanged } from "../settingsHelpers";

    let dropdownOpen = false;

    let loaderState : 'none' | 'loading' | 'success' | 'error' = 'none';

    function getAuthorName(user: User) {
        const primaryLang = getPrimaryLanguage()
        const variant = user.variants.find(v => v.language_id === primaryLang.id);
        return variant?.name || 'Unknown user';
    }

    function saveAuthors() {
        loaderState = 'loading';

        updatePostAuthors($postStore.authors)
            .then(() => loaderState = 'success')
            .catch(() => loaderState = 'error');
    }

    function handleRemoveAuthor(authorId: number) {
        updatePostStore({
            authors: $postStore.authors.filter(a => a.id !== authorId)
        });

        if ($postVariantStore.status !== 'published')
            saveAuthors();
    }

    function handleAddAuthor(e: CustomEvent<User>) {
        dropdownOpen = false;

        const author = e.detail;
        if ($postStore.authors.find(a => a.id === author.id)) return;

        updatePostStore({
            authors: [...$postStore.authors, author]
        });

        if ($postVariantStore.status !== 'published')
            saveAuthors();

    }

    $: hasAuthorsChanged  = hasIdArrayChanged($postStore.authors, $postOriginalStore.authors);

</script>

<OnlyPrimaryVariant>

    <SplitControl>
        <span slot="label">
            Authors

            <UnsavedTag
                show={hasAuthorsChanged}
                loaderState={loaderState}
            />

        </span>

        <div class="authors">

            <div class="left">

                {#if $postStore.authors.length}

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
                                color="red"
                                variant="invisible"
                                on:click={() => handleRemoveAuthor(author.id)}
                                size={16}
                                slot="end"
                            >
                                <IconX size={12} />
                            </IconButton>
                        </Tag>
                    {/each}

                {:else}
                    <Text light small>No authors</Text>
                {/if}

            </div>

            <div class="right">

                <Dropdown
                    position="bottom"
                    align="end"
                    width={300}
                    bind:show={dropdownOpen}
                >
                    <IconButton
                        color="input"
                        size={22}
                        slot="trigger"
                    >
                        <IconPlus size={14} />
                    </IconButton>

                    <AuthorsSearch 
                        slot="content"
                        on:select={handleAddAuthor}
                    />
                
                </Dropdown>

            </div>

        </div>


    </SplitControl>
</OnlyPrimaryVariant>

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

</style>