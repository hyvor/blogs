<script lang="ts">
	import { Button, ButtonGroup, Callout, CodeBlock, Link, Loader, Modal, SplitControl, toast } from "@hyvor/design/components";
    import UpgradeRequired from "../../billing/UpgradeRequired.svelte";
	import { loadHyvorTalk, type HyvorTalkIntegrationData, createHyvorTalkIntegration, deleteHyvorTalkIntegration } from "./hyvorTalkActions";
	import { onMount } from "svelte";
	import { blogStore } from "../../../lib/stores/blogStore";
	import AddCommentsEmbedCode from "./AddCommentsEmbedCode.svelte";

    let isLoading = true;
    let data: HyvorTalkIntegrationData;

    let isConnecting = false;
    let isDisconnecting = false;

    let addingCommentsEmbedCode = false;

    function handleConnect() {
        
        isConnecting = false;
        const toastId = toast.loading('Connecting to Hyvor Talk...');

        createHyvorTalkIntegration()
            .then(res => {
                data = {
                    connected: true,
                    data: res
                }
                toast.success('Hyvor Talk connected successfully', {id: toastId});
            })
            .catch(_ => toast.error('Failed to connect to Hyvor Talk', {id: toastId}));

    }

    function handleDisconnect() {

        isDisconnecting = false;
        const toastId = toast.loading('Disconnecting from Hyvor Talk...');

        deleteHyvorTalkIntegration()
            .then(_ => {
                data = {
                    connected: false,
                }
                toast.success('Hyvor Talk disconnected successfully', {id: toastId});
            })
            .catch(_ => toast.error('Failed to disconnect from Hyvor Talk', {id: toastId}));

    }

    $: embedCode = data && data.connected ? `<` + `script async src="https://talk.hyvor.com/embed/embed.js" type="module"></` + `script>
<hyvor-talk-comments 
    website-id="${data.data.website_id}" 
    page-id="{{ _post.id }}"
    page-url="{{ _post.url }}"
></hyvor-talk-comments>` : '';

    async function handleCopy() {
        await navigator.clipboard.writeText(embedCode);
        toast.success('Copied to clipboard');
    }

    onMount(() => {
        loadHyvorTalk()
            .then(res => data = res)
            .catch(_ => toast.error("Failed to load Hyvor Talk integration data"))
            .finally(() => isLoading = false);
    });

</script>


<UpgradeRequired
    minPlan="growth"
    trialAllowed={false}
    allow={true}
>

    <div slot="upgrade-text">
        This integration allows you to use <a href="https://talk.hyvor.com" target="_blank" style="text-decoration:underline">Hyvor Talk</a> on your blog for FREE. Upgrade to the Growth plan or higher to use this integration. This integration is not available in the trial period.
    </div>

    {#if isLoading}
        <Loader full />
    {:else}

        <SplitControl label="Introduction">

            <div>            
                <Link href="https://talk.hyvor.com" target="_blank">Hyvor Talk</Link> is a privacy-first commenting platform. You can use it for free on your blog.

                <p>
                    When you connect Hyvor Talk to your blog, we will automatically create a new website ID in Hyvor Talk for this blog under your account. You can manage the comments from the Hyvor Talk Console.
                </p>
            </div>

        </SplitControl>

        <SplitControl label="Connect Hyvor Talk">

            {#if data.connected}

                <div class="connection-status">
                    This blog is connected to website ID <strong>{data.data.website_id}</strong> in Hyvor Talk. You can manage comments from the Hyvor Talk Console.
                </div>

                <Button
                    as="a"
                    href={`https://talk.hyvor.com/console/${data.data.website_id}/comments`}
                    target="_blank"
                    size="small"
                    style="margin-right:6px;"
                >
                    Go to Hyvor Talk Console
                </Button>
                
                <Button
                    color="danger"
                    size="small"
                    on:click={() => isDisconnecting = true}
                >Disconnect</Button>

            {:else}

                <div class="connection-status">
                    This blog is not connected to a website in Hyvor Talk.
                </div>

                <Button on:click={() => isConnecting = true}>
                    Connect Now
                </Button>

            {/if}

        </SplitControl>

        {#if data.connected}

            <div class="embed-code">

                <SplitControl label="Embed Code">

                    <p style="margin-top:0;">
                        Add the embed code to <Link style="display:inline;" underline href={`/console/${$blogStore.subdomain}/settings/comments`}>Comments Embed Code</Link> to load Hyvor Talk on all posts.
                    </p>

                    <CodeBlock code={embedCode} />

                    <div>

                        <Button size="small" on:click={() => addingCommentsEmbedCode = true}>
                            Add to "Comments Embed Code"
                        </Button>

                        <Button size="small" on:click={handleCopy}>
                            Copy code
                        </Button>

                    </div>

                    <p>
                        You can also add it directly into your theme files. Feel free to customize the code (see <Link underline href="https://talk.hyvor.com/docs/install" target="_blank">Hyvor Talk docs</Link>).
                    </p>

                </SplitControl>

            </div>

        

        {/if}

    {/if}

</UpgradeRequired>

{#if isConnecting}
    <Modal
        title="Connect Hyvor Talk"
        bind:show={isConnecting}
    >
        <div>
            <p>
                Please confirm that you want to create a website ID in Hyvor Talk for this blog.
            </p>
            <ul>
                <li>A new Hyvor Talk website ID will be created under your HYVOR account.</li>
                <li>This new website can <b>only</b> be used on this blog.</li>
                <li>It is free of charge.</li>
                <li>
                    If you have any other websites on Hyvor Talk, you will need a separate subscription.
                </li>
            </ul>
        </div>

        <svelte:fragment slot="footer">
            <ButtonGroup>
                <Button color="invisible" on:click={() => isConnecting = false}>Cancel</Button>
                <Button on:click={handleConnect}>Confirm</Button>
            </ButtonGroup>
        </svelte:fragment>
    </Modal>
{/if}

{#if isDisconnecting}
    <Modal 
        title="Disconnect Hyvor Talk"
        bind:show={isDisconnecting}
    >

        <Callout type="warning">
            You cannot connect this blog to the same website ID again.
        </Callout>

        <p>
            Are you sure you want to disconnect this blog from Hyvor Talk? This will not delete your Hyvor Talk Website ID. You will have to delete it manually from the Hyvor Talk Console.
        </p>

        <svelte:fragment slot="footer">
            <ButtonGroup>
                <Button color="invisible" on:click={() => isDisconnecting = false}>Cancel</Button>
                <Button color="danger" on:click={handleDisconnect}>Disconnect</Button>
            </ButtonGroup>
        </svelte:fragment>

    </Modal>
{/if}

{#if addingCommentsEmbedCode}
    <AddCommentsEmbedCode 
        bind:open={addingCommentsEmbedCode} 
        code={embedCode}    
    />
{/if}

<style>

    .connection-status {
        margin-bottom: 10px;
    }

    .embed-code :global(.split-control .right) {
        min-width: 0;
    }

</style>