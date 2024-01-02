<script lang="ts">
	import { ActionList, ActionListItem, Button, Dropdown, Loader, Tag, TextInput, Validation, toast } from "@hyvor/design/components";
	import { IconArrowReturnLeft, IconCaretDown } from "@hyvor/icons";
	import { createEventDispatcher, tick } from "svelte";
	import { getPosts } from "../../../../../../postActions";
	import type { Language, Post } from "../../../../../../../../lib/types";
	import { getPrimaryLanguage, languagesStore, primaryLanguageStore } from "../../../../../../../../lib/stores/languagesStore";
	import LanguageTag from "../../../../../Header/LanguageSelector/LanguageTag.svelte";

    let input = '';
    let currentLanguage = $primaryLanguageStore;
    let languageDropdownShow = false;

    let isLoading = false;
    let posts : Post[] = [];

    const dispatch = createEventDispatcher();

    function handleClick() {
        dispatch('add', input.trim());
    }

    let searchTimeout: null | ReturnType<typeof setTimeout> = null;

    function handleInput(e: Event) {
        const val = (e.target as HTMLInputElement).value;

        if (val.trim().length === 0) {
            isLoading = false;
            posts = [];
            return;
        }

        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadPosts, 250);
    }

    function loadPosts() {
        isLoading = true;
            posts = [];

            getPosts({
                search: input,
                limit: 30
            }).then(res => {
                posts = res;
                isLoading = false;
            }).catch(err => {
                toast.error(err.message);
                isLoading = false;
            });
    }


    function getCurrentVariant(post: Post) {
        return post.variants.find(v => v.language_id === currentLanguage.id) || {
            title: '(No title)',
            url: '(No url)'
        };
    }

    async function handleDropdownSelect(language: Language) {
        currentLanguage = language;
        await tick();
        languageDropdownShow = false;

        loadPosts();
    }


</script>

<div class="input-wrap">

    <TextInput
        placeholder="Search a post in your blog..."
        block
        autofocus
        bind:value={input}
        on:input={handleInput}
    />

    {#if $languagesStore.length > 1}

        <!-- svelte-ignore a11y-click-events-have-key-events -->
        <!-- svelte-ignore a11y-no-static-element-interactions -->
        <div on:click|stopPropagation>
            <Dropdown
                align="end"
                bind:show={languageDropdownShow}
            >
                <Button slot="trigger" color="gray">
                    { currentLanguage.name }
                    <IconCaretDown size={12} slot="end" />
                </Button>
                <ActionList 
                    slot="content"
                    selection="single"
                >
                    {#each $languagesStore as language}
                        <ActionListItem
                            selected={language.id === currentLanguage.id}
                            on:select={() => handleDropdownSelect(language)}
                        >
                            { language.name }
                        </ActionListItem>
                    {/each}
                </ActionList>
            </Dropdown>
        </div>

    {/if}

</div>

<div class="results">

    {#if isLoading}
        <Loader block padding={40} />
    {:else}

        {#if input.trim().length}

            {#each posts as post}

                <div class="post">

                    <div class="title">
                        {getCurrentVariant(post).title}
                    </div>

                    <div class="url">
                        {getCurrentVariant(post).url}
                    </div>

                </div>
                
            {/each}

        {/if}

    {/if}

</div>

<style>

    .input-wrap {
        display: flex;
        gap: 10px;
    }
    .results {
        margin: 15px 0;
        max-height: 400px;
        overflow-y: auto;
    }

    .post {
        padding: 15px 20px;
        cursor: pointer;
        border-radius: var(--box-radius);
    }
    .post:hover {
        background: var(--hover);
    }

    .post .title {
        font-weight: 600;
    }
    .post .url {
        font-size: 14px;
        color: var(--text-light);
        margin-top: 2px;
    }

</style>