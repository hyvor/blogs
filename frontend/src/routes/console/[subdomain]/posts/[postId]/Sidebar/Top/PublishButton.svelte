<script lang="ts">
	import { Button, Caption, FormControl, Label, Modal, Radio, TextInput, toast } from "@hyvor/design/components";
	import { IconSendFill } from "@hyvor/icons";
	import { postVariantStore } from "../../../postStore";
	import dayjs from "dayjs";
	import { updatePost, updatePostVariant } from "../../../postActions";

    let modalOpen = false;
    let type : 'published' | 'scheduled' = 'published';

    let scheduleDate = dayjs().format('YYYY-MM-DD');

    async function handlePublish() {
        modalOpen = false;

        const toastId = toast.loading('Publishing...');

        try {
            await updatePost({
                published_at: type === 'published' ? 
                    dayjs().unix() :
                    dayjs(scheduleDate).unix()
            })
        } catch (error) {
            toast.error('Failed to publish post (update post)', {id: toastId})
            return;
        }

        updatePostVariant({
            status: type
        }).then(() => {
            toast.success('Post published', {id: toastId})
        }).catch(() => {
            toast.error('Failed to publish post', {id: toastId})
        });

    }

</script>


{#if $postVariantStore.status === 'draft'}

    <Button
        color="accent"
        on:click={() => modalOpen = true}
        size="small"
    >
        Publish
        <IconSendFill slot="end" size={12} />
    </Button>


    <Modal
        title="Publish Post"
        bind:show={modalOpen}
        size="small"
    >

        <div class="modal-inner">

            <div style="display:flex;justify-content:space-between">
                <Radio name="radio" value="published" bind:group={type}>Publish now</Radio>
                <Radio name="radio" value="scheduled" bind:group={type}>Schedule for later</Radio>
            </div>

            {#if type === 'scheduled'}
                <FormControl style="margin-top:25px;">
                    <Label for="publish-time">Schedule Time</Label>
                    <!-- <Caption>Choose a time to publish</Caption> -->
                    <TextInput 
                        type="date" 
                        id="publish-time"
                        bind:value={scheduleDate}    
                    />
                </FormControl>
            {/if}


        </div>


        <div slot="footer">
            <Button 
                variant="invisible" 
                on:click={() => modalOpen = false}
            >
                Cancel
            </Button>
            <Button 
                color="accent" 
                on:click={handlePublish}
            >
                {type === 'published' ? 'Publish' : 'Schedule'}
            </Button>
        </div>


    </Modal>

{/if}