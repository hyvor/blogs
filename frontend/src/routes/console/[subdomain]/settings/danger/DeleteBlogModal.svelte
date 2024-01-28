<script lang="ts">
	import { Button, ButtonGroup, FormControl, Modal, TextInput, Validation, toast } from "@hyvor/design/components";
	import { blogStore } from "../../../lib/stores/blogStore";
	import { deleteBlogDangerous } from "./dangerActions";

    export let show = false;

    let subdomain = '';
    let error : null | string = null

    function handleDelete() {

        if (subdomain !== $blogStore.subdomain) {
            error = "Subdomain doesn't match";
            return;
        }

        show = false;

        const toastId = toast.loading('Please wait...');

        deleteBlogDangerous()
            .then(() => {
                toast.success(
                    'Blog deletion is in progress. It will take a few minutes to complete.',
                    { id: toastId }
                );
                setTimeout(() => {
                    window.location.href = '/';
                }, 3000);
            })
            .catch(() => {
                toast.error(
                    'Something went wrong. Please try again later.',
                    { id: toastId }
                );
            });
    
    }

</script>

<Modal 
    title="Delete Blog"
    bind:show={show}
>

    You are about to delete your blog. This action is <strong style="color:var(--red-dark)">irreversible</strong>. Make sure to backup your data before deleting the blog. Type the subdomain of this blog, <strong>{$blogStore.subdomain}</strong>, to confirm.

    <div style="margin-top:15px;">

        <FormControl>

            <TextInput 
                bind:value={subdomain}
                block
                placeholder={"Type '" + $blogStore.subdomain + "' to confirm"}
                state={
                    error ? 'error' :
                    subdomain === '' ? 'default' :
                        (subdomain === $blogStore.subdomain ? 'success' : 'error')
                }
            />

            {#if error}
                <Validation state="error">{error}</Validation>
            {/if}

        </FormControl>

    </div>

    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button
                variant="invisible"
                on:click={() => show = false}
            >
                Cancel
            </Button>

            <Button
                on:click={handleDelete}
                color="red"
            >
                Goodbye, Blog
            </Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>