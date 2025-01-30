<script lang="ts">
	import { FormControl, Loader, SplitControl, TextInput, Validation, toast } from "@hyvor/design/components";
	import { checkSlugAvailability } from "../tagActions";

    interface Props {
        id: number;
        slug: string;
        slugOriginal: string;
    }

    let { id, slug = $bindable(), slugOriginal }: Props = $props();

    let isLoading = $state(false);

    let validation : null | {
        state: 'success' | 'error',
        message: string
    } = $state(null);

    let timeout: null | ReturnType<typeof setTimeout> = null;
    let abortController : AbortController | null = null;

    function handleInput(e: any) {
        const val = e.target.value.trim();

        validation = null;
        isLoading = false;

        if (timeout) {
            clearTimeout(timeout);
        }
        if (abortController) {
            abortController.abort();
        }

        if (val === '') {
            validation = {
                state: 'error',
                message: 'Slug cannot be empty'
            }
            return;
        }

        if (val === slugOriginal) {
            return;
        }

        isLoading = true;

        setTimeout(() => {

            abortController = new AbortController();

            checkSlugAvailability(id, val, abortController.signal)
                .then(res => {
                    if (res.available) {
                        validation = {
                            state: 'success',
                            message: 'Slug is available'
                        }
                    } else {
                        validation = {
                            state: 'error',
                            message: 'Slug is not available'
                        }
                    }
                })
                .catch(() => {

                    if (abortController?.signal.aborted)
                        return;

                    toast.error('Failed to check slug availability');
                })
                .finally(() => {
                    isLoading = false;
                })
        }, 500);
       
    }

</script>

<SplitControl
    label="Slug"
    caption="The slug of the tag"
>

    <FormControl>
        <TextInput
            bind:value={slug}
            block
            state={validation?.state || 'default'}
            on:input={handleInput}
            maxlength={255}
        >
            {#snippet end()}
                        <Loader
                    
                    size="small"
                    colorTrack="transparent"
                    state={isLoading ? 'loading' : (validation?.state || 'none')}
                />
                    {/snippet}
        </TextInput>

        {#if validation}
            <Validation state={validation.state}>
                {validation.message}
            </Validation>
        {/if}

    </FormControl>

</SplitControl>