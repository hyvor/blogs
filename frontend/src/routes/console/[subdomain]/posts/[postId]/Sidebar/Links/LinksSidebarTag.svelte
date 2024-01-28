<script lang="ts">
	import { Loader, Tag } from "@hyvor/design/components";
    import { variantLinkCountsStore } from "./linksStore";
	import { IconCheckCircleFill, IconExclamationCircleFill, IconXCircleFill } from "@hyvor/icons";
    
    $: counts = $variantLinkCountsStore;
</script>

<span>
    {#if counts.loading > 0}
        <Loader size={10} />
    {:else if counts.broken > 0}
        <Tag size="small" color="red">
            {counts.broken}
            <IconXCircleFill slot="end" size={10} />
        </Tag>
    {:else if counts.redirect > 0}
        <Tag size="small" color="orange">
            {counts.redirect}
            <IconExclamationCircleFill slot="end" size={10} />
        </Tag>
    {:else if counts.ok > 0}
        <Tag size="small" color="green" >
            {counts.ok}
            <IconCheckCircleFill slot="end" size={10} />
        </Tag>
    {/if}
</span>

<style>
    span :global(span) {
        padding: 2px 6px!important;
        font-size: 11px!important;
    }
</style>