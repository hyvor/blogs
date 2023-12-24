<script lang="ts">
	import { Tag } from "@hyvor/design/components";
    import type { PostStatus } from "../../lib/types";
	import { IconCheck, IconHourglass, IconJournalText } from "@hyvor/icons";
    
    export let status: PostStatus;
    export let size: 'small' | 'medium' = 'small';

    $: color = {
        draft: 'orange',
        published: 'green',
        scheduled: 'blue',
    }[status] as any;

    $: icon = {
        draft: IconJournalText,
        published: IconCheck,
        scheduled: IconHourglass,
    }[status] as any;

</script>


<Tag
    color={color}
    size={size}
>

    <svelte:component this={icon} slot="start" size={12} />

    {status.toUpperCase()}
</Tag>