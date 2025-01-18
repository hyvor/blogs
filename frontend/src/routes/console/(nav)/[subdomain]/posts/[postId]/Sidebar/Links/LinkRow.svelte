<script lang="ts">
	import { IconEyeSlashFill } from '@hyvor/icons';
	import { IconArrowClockwise } from '@hyvor/icons';
	import { postEditingStatusStore, postVariantStore, updatePostVariantStore } from '../../../postStore';
	import { Tooltip, toast } from '@hyvor/design/components';
	import { IconPencilFill } from '@hyvor/icons';
	import { IconButton } from '@hyvor/design/components';
	import LinkStatusTag from '../../../../tools/link-analysis/Links/LinkStatusTag.svelte';
	import { focusLinkInEditor, isHttpLink, LINK_STATUS } from '../../../../../../lib/links/links';
	import { Loader } from '@hyvor/design/components';
	import { getStatusType, type Link } from '../../../../../../lib/links/links';
    import { variantLinkAnalysisStore } from './linksStore';
	import { callIgnoreLink, callLinkAnalysisApi } from "../../../../tools/link-analysis/linkAnalysisActions";

    $: linkStatus = $variantLinkAnalysisStore[link.originalHref] || LINK_STATUS.ERROR;
    $: linkStatusType = getStatusType(linkStatus);
    $: isHttp = isHttpLink(link);

    let isReloading = false;

    function handleReload() {

        isReloading = true;

        callLinkAnalysisApi($postVariantStore.id, [link.originalHref])
            .then(res => {

                const status = res.find(l => l.url === link.originalHref)?.status_code || LINK_STATUS.ERROR;

                updatePostVariantStore({
                    'link_analysis': {
                        ...$postVariantStore.link_analysis,
                        [link.originalHref]: status,
                    }
                });

            })
            .catch(() => {

                updatePostVariantStore({
                    'link_analysis': {
                        ...$postVariantStore.link_analysis,
                        [link.originalHref]: LINK_STATUS.ERROR,
                    }
                });

            })
            .finally(() => {
                isReloading = false;
            })

    }
    
    function handleIgnore() {

        isReloading = true;

        const status = linkStatusType === 'ignored' ? false : true

        callIgnoreLink($postVariantStore.id, link.originalHref, status)
            .then(res => {

                updatePostVariantStore({
                    'link_analysis': {
                        ...$postVariantStore.link_analysis,
                        [link.originalHref]:  status ? LINK_STATUS.IGNORED : res.status_code,
                    }
                });

            })
            .catch(e => {
                toast.error(e.message)
            })
            .finally(() => {
                isReloading = false;
            })

    }

    export let link: Link;
</script>

<div class="link-wrap type-{linkStatusType}">

    <div class="link-name">
        <div class="link-anchor">
            {link.anchor}
        </div>

        <div class="link-url">
            <a 
                href={link.href} 
                target="_blank" 
                rel="nofollow"
            >
                {link.originalHref}
            </a>
        </div>

        <div class="link-type-wrap">
            <span class="link-type">{link.type}</span>
        </div>
    </div>

    <div class="link-status">
        {#if linkStatusType === 'loading' || isReloading}
            <Loader size="small" />
        {:else}
            <LinkStatusTag 
                status={linkStatus} 
                isAnchor={link.type === 'anchor'} 
            />
        {/if}
    </div>

    <div class="link-buttons">

        <Tooltip text="Edit in Editor">
            <IconButton 
                size={22}
                color="input"
                on:click={() => focusLinkInEditor(link, $postEditingStatusStore.editorView)}
            >
                <IconPencilFill size={12} />
            </IconButton>
        </Tooltip>

        <Tooltip text="Recheck">
            <IconButton 
                size={22}
                color="input"
                on:click={handleReload}
                disabled={!isHttp || isReloading}
            >
                <IconArrowClockwise size={12} />
            </IconButton>
        </Tooltip>

        <Tooltip text={(linkStatusType === 'ignored' ? 'Unignore' : 'Ignore') + ' this link'}>
            <IconButton 
                size={22}
                color={linkStatusType === 'ignored' ? 'accent' : 'input'}
                on:click={handleIgnore}
                disabled={!isHttp || isReloading}
            >
                <IconEyeSlashFill size={12} />
            </IconButton>
        </Tooltip>

    </div>

</div>

<style lang="scss">
    .link-wrap {
        display: flex;
        padding: 10px 0;
        align-items: center;
        border-bottom: 1px solid var(--accent-lightest);

        .link-name {
            flex: 1;
            min-width: 0;
            word-wrap: break-word;
            margin-right: 5px;
            .link-url {
                font-size: 14px;
                a {
                    color: var(--link);
                    text-decoration: underline;
                }
            }
            .link-type-wrap {
                margin-top: 3px;
            }
            .link-type {
                background-color: #eee;
                display: inline-block;
                padding: 3px 10px;
                border-radius: 20px;
                font-size: 9px;
                font-weight: 600;
                text-transform: uppercase;
            }
        }

        .link-buttons {
            margin-left: 10px;
            margin-right: 3px;
        }

    }
</style>