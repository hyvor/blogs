<script lang="ts">
	import UnsavedTag from './UnsavedTag.svelte';
	import { FormControl, Loader, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
	import { postOriginalStore, postOriginalVariantStore, postStore, postVariantStore, updatePostVariantStore } from "../../../postStore";
	import { onMount } from "svelte";
	import consoleApi from "../../../../../lib/consoleApi";
	import { updatePostVariant } from "../../../postActions";

    let error: null | string = null;
    let warning: null | string = null;

    let isSaving = false;

    let loaderState : 'none' | 'loading' | 'success' | 'error' = 'none';


    function setErrorWarning(val: string) {
        error = null;
        warning = null;

        val = val.trim();

        if (val.includes('/')) {
            error = 'Slug cannot contain "/"';
        } else if (val.includes(' ')) {
            warning = 'Tip: Use "-" instead of spaces';
        }
    }

    function handleInput(e: any) {
        let val = e.target.value as string
        updatePostVariantStore({slug: val});
        setErrorWarning(val);        
    }

    function handleBlur(e: any) {

        const slug = (e.target.value as string).trim();

        if (!slug)
            return;

        if (slug === $postOriginalVariantStore.slug)
            return;

        loaderState = 'loading';
        
        consoleApi.get<{available: boolean}>({
            endpoint: `/post/${$postStore.id}/slug-available`,
            data: {
                language_id: $postVariantStore.language_id,
                slug
            }
        }).then(res => {
            if (!res.available) {
                loaderState = 'error';
                error = 'Slug is already taken';
            } else {
                if ($postVariantStore.status === 'draft') {
                    updatePostVariant({slug})
                        .catch(err => {
                            loaderState = 'error';
                            error = err.message;
                        })
                        .finally(() => {
                            loaderState = 'success';
                        })
                } else {
                    loaderState = 'success';
                }
            }
        }).catch(() => {
            loaderState = 'error';
        })

    }

    onMount (() => {
        setErrorWarning($postVariantStore.slug || '');
    })

</script>


<SplitControl>
    <span slot="label">
        Slug

        {#if $postVariantStore.slug !== $postOriginalVariantStore.slug}
            <UnsavedTag />
        {/if}
    </span>

    <FormControl>
        <TextInput
            block
            value={$postVariantStore.slug}
            on:input={handleInput}
            on:blur={handleBlur}
            maxlength={255}
            state={error ? 
                'error' : 
                (warning ? 'warning' : 'default')
            }
        >

            <span slot="end">
                <Loader 
                    size="small" 
                    colorTrack="var(--input)"
                    state={loaderState}
                />
            </span>

        </TextInput>

        {#if error}
            <Validation state="error">{error}</Validation>
        {/if}

        {#if warning}
            <Validation state="warning">{warning}</Validation>
        {/if}

    </FormControl>

</SplitControl>