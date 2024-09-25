<script lang="ts">
	import { Modal, TextInput } from "@hyvor/design/components";
    import type { Media } from "../../../lib/types";

    export let show = true;
    export let media : Media;
    export let handleChangeName;
</script>

<div>
    <Modal bind:show={show} size="large" closeOnEscape={false}>
        <TextInput bind:value={media.name} label="File Name" />
        <button on:click={handleChangeName}>
            Change
        </button>
    </Modal>
</div>

