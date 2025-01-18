<script lang="ts">
	import { Loader, Tag, Tooltip } from "@hyvor/design/components";
	import { IconCheck, IconHourglass, IconJournalText } from "@hyvor/icons";
	import type { PostVariant } from "../../../../lib/types";
	import { languagesStore } from "../../../../lib/stores/languagesStore";

    export let variant: PostVariant;

    $: language = $languagesStore.find(v => v.id === variant.language_id);

    let tooltip = '';
    let icon: any;
    let iconProps = {};

    $: {
        iconProps = {};

        if (language) {
            if (variant.status === 'published') {
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
    }

</script>


{#if language}

    <Tooltip text={tooltip} position="bottom">

        <Tag
            size="small"
            interactive 
            color="default"
            {...$$restProps}
        >
            {language.code}
            <svelte:component 
                this={icon} 
                size={10}
                slot="end" 
                {...iconProps} 
            />
        </Tag>

    </Tooltip>

{/if}