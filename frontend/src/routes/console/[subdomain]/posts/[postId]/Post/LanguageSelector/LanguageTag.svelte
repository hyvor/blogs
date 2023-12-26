<script lang="ts">
	import { Tag, Tooltip } from "@hyvor/design/components";
	import type { Language } from "../../../../../lib/types";
	import { IconCheck, IconHourglass, IconJournalText, IconPlus } from "@hyvor/icons";
	import { postStore } from "../../../../../lib/stores/postStore";

    export let language: Language;
    export let active: boolean = false;

    let tooltip = '';
    let icon: any;

    $: {
        const variant = $postStore.variants.find(v => v.language_id === language.id);

        if (!variant) {
            icon = IconPlus;
            tooltip = `Add ${language.name} translation`;
        } else if (variant.status === 'published') {
            icon = IconCheck;
            tooltip = `${language.name} - Published`;
        } else if (variant.status === 'draft') {
            icon = IconJournalText;
            tooltip = `${language.name} - Draft`;
        } else if (variant.status === 'scheduled') {
            icon = IconHourglass
            tooltip = `${language.name} - Scheduled`;
        }
    }

</script>


<Tooltip text={tooltip} position="bottom">

    <Tag size="medium" interactive color={active ? "accent" : "default"}>
        {language.code}
        <svelte:component this={icon} size={12} slot="end" />
    </Tag>

</Tooltip>