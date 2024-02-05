<script lang="ts">
	import { IconCheckCircleFill, IconInfoCircle, IconInfoCircleFill, IconXCircleFill } from "@hyvor/icons";
    import { plansMax, type Feature, plansStart } from "./pricing";
	import { Tooltip } from "@hyvor/design/components";
    export let features : Feature[];
    export let title: string;
</script>

<div class="title">{title}</div>

{#each features as feature}

    <div class="feature">
        <div class="name">
            {feature.name}

            {#if feature.description}
                <Tooltip text={feature.description}>
                    <IconInfoCircle />
                </Tooltip>
            {/if}

        </div>

        {#each feature.values as value, i}
            {#if i >= $plansStart && i < $plansMax + $plansStart}
                <div class="value">
                    <div>
                        {#if value === true}
                            <IconCheckCircleFill style="color:var(--accent)" />
                        {:else if value === false}
                            <IconXCircleFill style="color:var(--gray)" />
                        {:else}
                            {value}
                        {/if}
                    </div>
                </div>
            {/if}
        {/each}
    </div>

{/each}

<style lang="scss">
    .title {
        font-weight: 600;
        margin-top: 30px;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }
    .feature {
        display: flex;
        padding: 13px 0;
        .name {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .value {
            flex: 1;
            > div {
                padding: 0 25px;
            }
        }
    }
    @media (max-width: 1000px) {
        .name {
            flex: 2!important;
        }
        .value {
            flex: 3!important;
        }
    }
</style>