<script lang="ts">
	import { Loader, Tag, Tooltip } from "@hyvor/design/components";
	import type { Language } from "../../../../../lib/types";
	import { IconCheck, IconHourglass, IconJournalText, IconPlus } from "@hyvor/icons";
	import { postStore } from "../../../postStore";

    export let language: Language;
    export let active: boolean = false;
    export let isCreating = false;
    export let size : 'small' | 'medium' = 'medium';

    let tooltip = '';
    let icon: any;
    let iconProps = {};

    $: {
        const variant = $postStore.variants.find(v => v.language_id === language.id);

        iconProps = {};

        if (isCreating) {
            icon = Loader;
            iconProps = { invert: false, colorTrack: 'transparent' }
            tooltip = `Creating ${language.name} translation`;
        } else if (!variant) {
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

    <Tag
        size={size}
        interactive 
        color={active ? "accent" : "default"}
        {...$$restProps}
    >
        {language.code}
        <svelte:component 
            this={icon} 
            size={size === 'small' ? 10 : 12}
            slot="end" 
            {...iconProps} 
        />
    </Tag>

</Tooltip>