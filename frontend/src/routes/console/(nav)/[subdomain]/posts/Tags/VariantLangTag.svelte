<script lang="ts">
    import { run } from 'svelte/legacy';

	import { Loader, Tag, Tooltip } from "@hyvor/design/components";
	import { IconCheck, IconHourglass, IconJournalText } from "@hyvor/icons";
	import type { PostVariant } from "../../../../lib/types";
	import { languagesStore } from "../../../../lib/stores/languagesStore";

    interface Props {
        variant: PostVariant;
        [key: string]: any
    }

    let { variant, ...rest }: Props = $props();

    let language = $derived($languagesStore.find(v => v.id === variant.language_id));

    let tooltip = $state('');
    let icon: any = $state();
    let iconProps = $state({});

    run(() => {
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
    });

</script>


{#if language}

    <Tooltip text={tooltip} position="bottom">

        <Tag
            size="small"
            interactive 
            color="default"
            {...rest}
        >
            {language.code}
            {#snippet end()}
                        {@const SvelteComponent = icon}
            <SvelteComponent 
                    size={10}
                     
                    {...iconProps} 
                />
                    {/snippet}
        </Tag>

    </Tooltip>

{/if}