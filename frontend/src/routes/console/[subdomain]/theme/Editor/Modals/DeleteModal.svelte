<script lang="ts">
	import { Button, ButtonGroup, Modal, toast } from "@hyvor/design/components";
	import type { ThemeFile } from "../../../../lib/types";
	import { deleteFile } from "../../../../lib/actions/themeActions";
	import { removeThemeFileStore, selectedThemeFileIdStore } from "../../themeStore";

    export let open = false;
    export let file: ThemeFile;

    function handleDelete() {
        open = false;
        const toastId = toast.loading('Deleting file...');

        deleteFile(file.id)
            .then(() => {
                toast.success('File deleted successfully', {id: toastId});
                removeThemeFileStore(file.id);
                selectedThemeFileIdStore.set(null);
            });
    }
</script>


<Modal size="small" bind:show={open} title="Delete file">

    Are you sure to delete this file?

    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button color="invisible" on:click={() => open = false}>
                Cancel
            </Button>

            <Button
                color="danger" 
                on:click={handleDelete}
            >
                Delete
            </Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>