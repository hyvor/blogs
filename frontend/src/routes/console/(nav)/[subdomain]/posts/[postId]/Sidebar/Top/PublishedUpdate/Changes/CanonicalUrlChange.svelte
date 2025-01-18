<script lang="ts">
	import { SplitControl, Text } from "@hyvor/design/components";
    import { IconArrowRight } from "@hyvor/icons";

    interface Props {
        canonicalUrlOld: string | null;
        canonicalUrlNew: string | null;
        diff: boolean;
    }

    let { canonicalUrlOld, canonicalUrlNew, diff }: Props = $props();
</script>

<SplitControl label="Canonical URL">
    <div class="wrap">

        {#if diff}
            <span class="before">
                {#if canonicalUrlOld}
                    {canonicalUrlOld}
                {:else}
                    <Text small light>Empty</Text>
                {/if}   
            </span>
            <IconArrowRight />
        {/if}

        <span class="after">
            {#if canonicalUrlNew}
                {canonicalUrlNew}
            {:else}
                <Text small light>Empty</Text>
            {/if}
        </span>

    </div>
</SplitControl>

<style>
    .wrap {
        display: flex;
        align-items: center;
    }
    .wrap :global(svg) {
        margin: 0 15px;
    }
    .after {
        font-weight: 600;
    }
</style>