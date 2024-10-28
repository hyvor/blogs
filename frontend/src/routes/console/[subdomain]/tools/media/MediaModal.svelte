<script lang="ts">
     import { Modal, TextInput, FormControl } from "@hyvor/design/components";

     export let show : boolean;
     export let handleChangeName : () => void;
     export let media : any;
</script>

<div>
<Modal
    bind:show={show} 
    closeOnEscape={false} 
    title="Media name edition"
    footer={{
        cancel: {
            text: 'Cancel',
        },
        confirm: {
            text: 'Change',
            danger: true
        }
    }}
    on:cancel={() => show = false}
    on:confirm={handleChangeName}
    >
    <FormControl>
        <TextInput bind:value={media.name} label="File Name"/>
    </FormControl>
</Modal>
</div>