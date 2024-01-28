<script lang="ts">
	import { Tooltip } from "@hyvor/design/components";
    import { calculateLinkAnalysis, getCountsByStatus } from "../../../lib/links/links";
	import type { PostVariant } from "../../../lib/types";
	import { IconCheckCircleFill, IconExclamationCircleFill, IconQuestionCircle, IconQuestionCircleFill, IconXCircleFill } from "@hyvor/icons";

    export let postVariant: PostVariant;
    const linkAnalysis = calculateLinkAnalysis(postVariant);
    const counts = getCountsByStatus(linkAnalysis);
</script>


{#if counts.loading > 0}
    <Tooltip 
        text="Link analysis outdated. Open the post to analyze again"
    >
        <IconQuestionCircleFill size={12} />
    </Tooltip>
{:else if counts.broken > 0}
    <Tooltip 
        text="Broken links found"
    >
        <IconXCircleFill size={12} style="color:var(--red)" />
    </Tooltip>
{:else if counts.redirect > 0}
    <Tooltip 
        text="Redirect links found"
    >
        <IconExclamationCircleFill size={12} style="color:var(--orange)" />
    </Tooltip>
{:else}
    <Tooltip 
        text="All links are healthy"
    >
        <IconCheckCircleFill size={12} color="var(--green)" />
    </Tooltip>
{/if}