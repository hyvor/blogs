<script lang="ts">
    import { run } from 'svelte/legacy';

	import { Tag, Tooltip } from "@hyvor/design/components";
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
import IconExclamationCircleFill from '@hyvor/icons/IconExclamationCircleFill';
import IconEyeSlashFill from '@hyvor/icons/IconEyeSlashFill';
import IconXCircleFill from '@hyvor/icons/IconXCircleFill';

	import { getStatusType } from "../../../../../lib/links/links";

    interface Props {
        status: number;
        isAnchor?: boolean;
        showTooltip?: boolean;
    }

    let { status, isAnchor = false, showTooltip = true }: Props = $props();

    let statusType = $derived(getStatusType(status));
    let statusDisplay = $state("");
    let tooltip = $state("");
    let color : any = $state('default');

    run(() => {


        if (statusType === "ok") {
            statusDisplay = "OK";
            tooltip = isAnchor ?
                'Heading ID found' :
                "OK - HTTP status " + status;
            color = 'green';
        } else if (statusType === "redirect") {
            statusDisplay = "Redirect";
            tooltip = "Redirect status " + status;
            color = 'orange';
        } else if (statusType === "broken") {
            statusDisplay = "Broken";
            tooltip = isAnchor ? 
                'Heading ID not found' :
                "HTTP status " + status;
            color = 'red';
        } else if (statusType === 'ignored') {
            statusDisplay = "Ignored";
            tooltip = "Link Ignored";
            color = 'default';
        } else if (statusType === 'error') {
            statusDisplay = "Error";
            tooltip = "Error (on our side)";
            color = 'red';
        }

    });

</script>


<Tooltip text={tooltip}>

    <Tag 
        size="small"
        color={color}
    >

        {statusDisplay}

        {#snippet end()}
                <span  class="icon">
                {#if statusType === "ok"}
                    <IconCheckCircleFill size={12} />
                {:else if statusType === "redirect"}
                    <IconExclamationCircleFill size={12} />
                {:else if statusType === "broken"}
                    <IconXCircleFill size={12} />
                {:else if statusType === "ignored"}
                    <IconEyeSlashFill size={12} />
                {:else if statusType === "error"}
                    <IconXCircleFill size={12} />
                {/if}
            </span>
            {/snippet}

    </Tag>
    
</Tooltip>

<style>
    .icon {
        display: inline-flex;
        align-items: center;
    }
</style>