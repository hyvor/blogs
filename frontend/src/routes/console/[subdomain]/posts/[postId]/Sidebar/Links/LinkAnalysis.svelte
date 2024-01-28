<script lang="ts">
	import { IconMessage } from '@hyvor/design/components';
	import { IconEyeSlashFill } from '@hyvor/icons';
	import { IconXCircleFill } from '@hyvor/icons';
	import { IconExclamationCircleFill } from '@hyvor/icons';
	import { Text } from '@hyvor/design/components';
	import { IconCheckCircleFill } from '@hyvor/icons';
	import { Tag } from '@hyvor/design/components';
	import { IconArrowClockwise } from '@hyvor/icons';
	import { Loader } from '@hyvor/design/components';
	import { Button } from '@hyvor/design/components';
    import { variantLinksStore, variantLinkCountsStore } from './linksStore';
	import LinkRow from "./LinkRow.svelte";

    $: linksCount = $variantLinksStore.length;

    let isReloadingAll = false;

    function handleJump(type: 'ok' | 'broken' | 'redirect' | 'ignored') {
        
    }

</script>

<div class="wrap">

    <div class="top">

        <div class="title">
            Links ({linksCount})
        </div>

        <div class="recheck-wrap">
            {#if linksCount > 0}
                <Button
                    size="small"
                >
                    <IconArrowClockwise size={14} slot="start" />
                    Recheck All
                    <Loader slot="end" state={isReloadingAll ? 'loading' : 'none'} />
                </Button>
            {/if}
        </div>

    </div>

    <div class="summary">

        {#if $variantLinkCountsStore.ok > 0}
            <Tag size="small" color="green" interactive on:click={() => handleJump('ok')}>
                <Text bold slot="start">{$variantLinkCountsStore.ok}</Text>
                OK
                <IconCheckCircleFill slot="end" size={12} />
            </Tag>
        {/if}

        {#if $variantLinkCountsStore.broken > 0}
            <Tag size="small" color="red">
                <Text bold slot="start">{$variantLinkCountsStore.broken}</Text>
                Broken
                <IconXCircleFill slot="end" size={12} />
            </Tag>
        {/if}

        {#if $variantLinkCountsStore.redirect > 0}
            <Tag size="small" color="orange">
                <Text bold slot="start">{$variantLinkCountsStore.redirect}</Text>
                Redirect
                <IconExclamationCircleFill slot="end" size={12} />
            </Tag>
        {/if}

        {#if $variantLinkCountsStore.ignored > 0}
            <Tag size="small" color="default">
                <Text bold slot="start">{$variantLinkCountsStore.ignored}</Text>
                Ignored
                <IconEyeSlashFill slot="end" size={12} />
            </Tag>
        {/if}

    </div>

    <div class="links">
        {#if linksCount > 0}
            {#each $variantLinksStore as link}
                <LinkRow {link} />
            {/each}
        {:else}
            <IconMessage 
                empty 
                message="No links found in your post" 
                padding={60}
                iconSize={60}
            />
        {/if}
    </div>

</div>

<style lang="scss">
    .wrap {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .top {
        display: flex;
        align-items: center;
        .title {
            font-weight: 600;
            flex: 1;
        }
        .recheck-wrap {
            :global(button) {
                font-size:12px!important;
            }
        }
    }
    .links {
        flex: 1;
        overflow: auto;
    }
</style>