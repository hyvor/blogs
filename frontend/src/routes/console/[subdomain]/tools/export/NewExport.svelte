<script lang="ts">
	import { Button, FormControl, Radio, SplitControl, confirm, toast } from "@hyvor/design/components";
	import { startExport, type ExportFormat } from "./exportActions";
	import { createEventDispatcher } from 'svelte';

    const dispatch = createEventDispatcher();

    function dispatchComplete() {
        dispatch('complete');
    }

    async function exportNow() {

        const formatName = format === 'hyvor_blogs' ? 'Hyvor Blog JSON' : 'WordPress';

        if (await confirm({
            title: 'Export Data',
            content: `You are about to export your data in the ${formatName} format. This may take a few minutes. You can track the progress in Export History.`,
            confirmText: 'Export Now'
        })) {

            const toastId = toast.loading('Exporting...');

            startExport(format)
                .then(() => {
                    toast.success(
                        'Export started, you can track the progress in Export History.', 
                        {id: toastId}
                    )
                    dispatchComplete();
                })
                .catch(() => toast.error(
                    'Failed to start export. Please try again later.', 
                    {id: toastId})
                );

        }
    }

    let format : ExportFormat = 'hyvor_blogs';

</script>


<SplitControl
    label="Export Format"
>
    <FormControl>
        <Radio value="hyvor_blogs" bind:group={format}>
            Hyvor Blog JSON
        </Radio>
        <Radio value="wordpress" bind:group={format}>
            WordPress
        </Radio>
    </FormControl>
</SplitControl>

<div class="button-wrap">
    <Button on:click={exportNow}>
        Export Now
    </Button>
</div>


<style>
    .button-wrap {
        padding: 30px;
        text-align: center;
    }
</style>