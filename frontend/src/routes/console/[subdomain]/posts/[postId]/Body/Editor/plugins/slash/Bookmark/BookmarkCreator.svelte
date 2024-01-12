<script lang="ts">
	import { Loader, Modal, TextInput, Button, Validation } from "@hyvor/design/components";
    import { createEventDispatcher } from "svelte";
	import { getUrlData } from "../../../../../../../../lib/actions/urlDataActions";
	import type { UrlData } from "../../../../../../../../lib/types";
	import { IconArrowReturnLeft } from "@hyvor/icons";
	import { isValidUrl } from "../../../../../../../../lib/helper/is-valid-url";
	import BookmarkDisplay from "./BookmarkDisplay.svelte";

    let show = true;
    let url = '';

    let inputEl : HTMLInputElement;
    let inputStarted = false;

    const dispatch = createEventDispatcher<{
        close: void,
        create: string
    }>();

    $: if (!show) {
        dispatch('close');
    }

    let isFetching = false;
    let error : null | string = null;

    let urlData : null | UrlData = null;
    
    function handleFetch() {

        if (!inputStarted) {
            return;
        }

        error = null;
        urlData = null;

        if (url.trim() === '') {
            error = 'URL is required';
            inputEl.focus();
            return;
        }

        if (!isValidUrl(url)) {
            error = 'Invalid URL';
            inputEl.focus();
            return;
        }

        isFetching = true;

        getUrlData(url, 'link')
            .then(data => {
                urlData = data;
            })
            .catch(_ => {
                error = 'Failed to embed this URL';
            })
            .finally(() => {
                isFetching = false;
            });
    }

    function handleCreate() {
        dispatch('create', urlData!.original_url);
    }

</script>

<Modal
    bind:show={show}
    title="Create Bookmark"
    footer={{
        confirm: urlData ? {
            text: 'Create Bookmark',
        } : false,
        cancel: {
            text: 'Close'
        }
    }}
    on:confirm={handleCreate}
>

    <div class="input-wrap">
        <TextInput 
            placeholder="Enter any URL..."
            autofocus
            block
            bind:value={url}
            on:keyup={e => {
                if (e.key === 'Enter') {
                    handleFetch();
                } else {
                    inputStarted = true;
                }
            }}
            state={error ? 'error' : undefined}
            bind:input={inputEl}
        />
        <Button on:click={handleFetch}>
            Fetch <IconArrowReturnLeft slot='end' />
        </Button>
    </div>

    {#if error} 
        <div style="margin-top:10px;">
            <Validation state="error">
                {error}
            </Validation>
        </div>
    {/if}

    {#if isFetching}
        <Loader block padding={50} />
    {/if}

    {#if urlData}
        <div class="display">
            <BookmarkDisplay {urlData} />
        </div>
    {/if}   

</Modal>

<style>
    .input-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .display {
        margin-top: 20px;
        overflow: auto;
        max-height: 400px;
    }
</style>