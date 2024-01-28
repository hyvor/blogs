<script lang="ts">
	import { Button, ButtonGroup, InputGroup, Modal, Radio, SplitControl, Textarea, toast } from "@hyvor/design/components";
	import { clearBlogCache } from "./dangerActions";

    export let show = false;

    let type : 'template' | 'paths' | 'all' = 'template';
    let paths = '';

    function handleClick() {

        const toastId = toast.loading('Clearing cache...');
        show = false;

        clearBlogCache({
            type,
            paths: paths.split('\n').filter(path => path.trim() !== '')
        }).then(() => {
            toast.success('Cache cleared.', {id: toastId});
        }).catch(err => {
            toast.error(err.message, {id: toastId});
        })

    }

</script>

<Modal  
    title="Clear Cache"
    bind:show={show}
>

    <SplitControl
        label="Type"
    >

        <InputGroup>
        
            <Radio
                bind:group={type}
                value="template"
            >
                Template
            </Radio>

            <Radio
                bind:group={type}
                value="paths"
            >
                Paths
            </Radio>

            <Radio
                bind:group={type}
                value="all"
            >
                All
            </Radio>

        </InputGroup>

        <p>
            <strong style="text-transform:capitalize">{type}</strong> type will
            {#if type === 'template'}
                clear cache of template pages (index, posts, pages), but not media and assets
            {:else if type === 'paths'}
                clear cache of the paths added below.
            {:else if type === 'all'}
                clear all cache, including media and assets
            {/if}
        </p>

    </SplitControl>

    {#if type === 'paths'}
            
        <SplitControl
            label="Paths"
            caption="Paths to clear cache"
        >
            <Textarea 
                bind:value={paths}
                placeholder="/assets/script.js
/assets/style.css"
            />
        </SplitControl>
    {/if}

    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button
                variant="invisible"
                on:click={() => show = false}
            >
                Cancel
            </Button>

            <Button
                on:click={handleClick}
            >
                Clear Cache
            </Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>