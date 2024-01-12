<script lang="ts">
	import { Button, IconMessage, LoadButton, Loader, TextInput, toast } from "@hyvor/design/components";
	import { IconArrowReturnLeft } from "@hyvor/icons";
	import type { UnsplashImage } from "../../../types";
	import { searchUnsplash } from "./unsplashActions";
	import { createEventDispatcher } from "svelte";
	import type { SelectedImage } from "../image-uploader";

    export let search = '';

    let isLoading = false;
    let isLoadingMore = false;
    let hasMore = false;
    let images : UnsplashImage[] = [];

    let hasLoaded = false;

    let inputError = false;

    const limit = 30;

    const dispatch = createEventDispatcher<{select: SelectedImage}>();

    function performSearch(page: number | undefined = 1) {
        inputError = false;

        if (search.trim() === '') {
            inputError = true;
            return;
        }

        page === 1 ? isLoading = true : isLoadingMore = true;

        searchUnsplash(search, page)
            .then(results => {
                images = page === 1 ? results : [...images, ...results];
                hasMore = results.length === limit;

                isLoading = false;
                isLoadingMore = false;

                hasLoaded = true;
            })
            .catch(e => {
                toast.error(e.message);
            })

    }

    function handleKeyup(e: KeyboardEvent) {
        if (e.key === 'Enter') {
            performSearch();
        }
    }

    function handleSelect(image: UnsplashImage) {
        dispatch('select', {
            url: image.url,
            name: null,
            size: null,
            from: 'unsplash',
            unsplash: image
        });
    }

    function handleLoadMore() {
        performSearch(Math.ceil(images.length / limit) + 1);
    }

</script>

<div class="unsplash">

    <div class="search-wrap">
        <TextInput 
            bind:value={search}
            placeholder="Search Unsplash"
            autofocus
            autocomplete="off"
            on:keyup={handleKeyup}
            block
            state={inputError ? 'error' : undefined}
        />
        <Button
            on:click={() => performSearch()}
        >
            Search <IconArrowReturnLeft slot="end" size={14} />
        </Button>
    </div>

    <div class="results">

        {#if isLoading}
            <Loader full />
        {:else}
            {#if images.length === 0 && search.trim() !== '' && hasLoaded}
                <IconMessage empty message="No images found" />
            {:else}
                <div class="cols">
                    {#each [0,1,2] as col (col)}
                        <div class="col">
                            {#each images as image, i (image.url)}
                                {#if i % 3 === col}
                                    <div 
                                        class="img-wrap" 
                                        on:click={() => handleSelect(image)}
                                        on:keyup
                                        role="button"
                                        tabindex="0"
                                    >
                                        <img 
                                            src={image.url} 
                                            alt={image.alt}
                                        />
                                    </div>
                                {/if}
                            {/each}
                        </div>
                    {/each}
                </div>
            {/if}
        {/if}

        <LoadButton 
            text="Load More"
            show={hasMore}
            loading={isLoadingMore}
            on:click={handleLoadMore}
        />

    </div>

</div>

<style lang="scss">
    .unsplash {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .search-wrap {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        gap: 10px;
    }
    .results {
        flex: 1;
        overflow-y: auto;
        .cols {
            display: flex;
            gap: 10px;
        }
        .col {
            flex: 1;
        }
        .img-wrap {
            margin-bottom: 10px;
            cursor: pointer;
            img {
                width: 100%;
                border-radius: 5px;
            }
        }
    }
</style>