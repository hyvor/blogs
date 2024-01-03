<script lang="ts">
	import { Tag, Tooltip } from "@hyvor/design/components";
	import { IconCheckCircleFill, IconExclamationCircleFill, IconEyeSlashFill, IconXCircleFill } from "@hyvor/icons";
	import { getStatusType } from "../../../../lib/links/links";

    export let status: number;
    export let isAnchor = false;
    export let showTooltip = true;

    $: statusType = getStatusType(status);
    let statusDisplay = "";
    let tooltip = "";
    let color : any = 'default';

    $: {


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

    }

</script>


<Tooltip text={tooltip}>

    <Tag 
        size="small"
        color={color}
    >

        {statusDisplay}

        <span slot="end" class="icon">
            {#if statusType === "ok"}
                <IconCheckCircleFill size={12} />
            {:else if statusType === "redirect"}
                <IconExclamationCircleFill size={12} />
            {:else if statusType === "broken"}
                <IconXCircleFill size={12} />
            {:else if statusType === "ignored"}
                <IconEyeSlashFill size={12} />
            {/if}
        </span>

    </Tag>
    
</Tooltip>

<style>
    .icon {
        display: inline-flex;
        align-items: center;
    }
</style>