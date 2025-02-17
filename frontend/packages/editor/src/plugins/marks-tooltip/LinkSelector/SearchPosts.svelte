<!-- 
TODO: FIX THIS
 
<script lang="ts">
    import {createBubbler, stopPropagation} from 'svelte/legacy';

    const bubble = createBubbler();
    import {
        ActionList,
        ActionListItem,
        Button,
        Dropdown,
        IconMessage,
        Loader,
        TextInput,
        toast
    } from '@hyvor/design/components';
    import IconCaretDown from '@hyvor/icons/IconCaretDown';
    import {createEventDispatcher, tick} from 'svelte';
    import {getPosts} from '../../../../../routes/console/(nav)/[subdomain]/posts/postActions';
    import type {Language, Post} from '../../../../../routes/console/lib/types';
    import {
        languagesStore,
        primaryLanguageStore
    } from '../../../../../routes/console/lib/stores/languagesStore';

    let input = $state('');
    let currentLanguage = $state($primaryLanguageStore);
    let languageDropdownShow = $state(false);

    let isLoading = $state(false);
    let posts: Post[] = $state([]);

    const dispatch = createEventDispatcher();

    let searchTimeout: null | ReturnType<typeof setTimeout> = null;

    function handleInput(e: Event) {
        const val = (e.target as HTMLInputElement).value;

        if (val.trim().length === 0) {
            isLoading = false;
            posts = [];
            return;
        }

        isLoading = true;

        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadPosts, 500);
    }

    function loadPosts() {
        isLoading = true;
        posts = [];

        getPosts({
            search: input,
            language_id: currentLanguage.id,
            status: 'published',
            limit: 30
        })
            .then((res) => {
                posts = res;
                isLoading = false;
            })
            .catch((err) => {
                toast.error(err.message);
                isLoading = false;
            });
    }

    function getCurrentVariant(post: Post) {
        return (
            post.variants.find((v) => v.language_id === currentLanguage.id) || {
                title: '(No title)',
                url: '(No url)'
            }
        );
    }

    async function handleDropdownSelect(language: Language) {
        currentLanguage = language;
        await tick();
        languageDropdownShow = false;

        if (input.trim().length) loadPosts();
    }

    function handleClick(post: Post) {
        const cur = getCurrentVariant(post);
        dispatch('add', cur.url);
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
        <!-- svelte-ignore a11y_click_events_have_key_events --
        <!-- svelte-ignore a11y_no_static_element_interactions --
        <div onclick={stopPropagation(bubble('click'))}>
            <Dropdown align="end" bind:show={languageDropdownShow}>
                {#snippet trigger()}
                    <Button color="gray">
                        {currentLanguage.name}
                        {#snippet end()}
                            <IconCaretDown size={12}/>
                        {/snippet}
                    </Button>
                {/snippet}
                {#snippet content()}
                    <ActionList selection="single">
                        {#each $languagesStore as language}
                            <ActionListItem
                                    selected={language.id === currentLanguage.id}
                                    on:select={() => handleDropdownSelect(language)}
                            >
                                {language.name}
                            </ActionListItem>
                        {/each}
                    </ActionList>
                {/snippet}
            </Dropdown>
        </div>
    {/if}
</div>

<div class="results">
    {#if isLoading}
        <Loader block padding={40}/>
    {:else if input.trim().length}
        {#if posts.length === 0}
            <IconMessage empty message="No posts found" padding={35} iconSize={60}/>
        {:else}
            {#each posts as post}
                <div
                        class="post"
                        role="button"
                        tabindex="0"
                        onclick={() => handleClick(post)}
                        onkeyup={bubble('keyup')}
                >
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
 -->
