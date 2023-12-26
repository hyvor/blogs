<script lang="ts">
	import { FormControl, Loader, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
	import { postOriginalVariantStore, postStore, postVariantStore, updatePostVariantStore } from "../../../../../../lib/stores/postStore";
	import { onMount } from "svelte";
	import consoleApi from "../../../../../../lib/consoleApi";
	import { updatePostVariant } from "../../../../../../lib/actions/postActions";

    let error: null | string = null;
    let warning: null | string = null;
    let isSaving = false;

    function setErrorWarning(val: string) {
        error = null;
        warning = null;

        val = val.trim();

        if (val.includes('/')) {
            error = 'Slug cannot contain "/"';
        } else if (val.includes(' ')) {
            console.log('here')
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
        
        consoleApi.get<{available: boolean}>({
            endpoint: `/post/${$postStore.id}/slug-available`,
            data: {
                language_id: $postVariantStore.language_id,
                slug
            }
        }).then(res => {
            if (!res.available) {
                error = 'Slug is already taken';
            } else {
                if ($postVariantStore.status === 'draft') {
                    isSaving = true;

                    updatePostVariant({slug})
                        .catch(err => {
                            toast.error(err.error);
                        })
                        .finally(() => {
                            isSaving = false;
                        })
                }
            }
        })

    }

    onMount (() => {
        setErrorWarning($postVariantStore.slug || '');
    })

</script>


<SplitControl>
    <span slot="label">Slug</span>

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
                {#if isSaving}
                    <Loader size="small" colorTrack="var(--input)" />
                {/if}
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