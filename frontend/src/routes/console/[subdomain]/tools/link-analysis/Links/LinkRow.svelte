<script lang="ts">
	import { IconButton, Link, Loader, TableRow, Tag, Tooltip, toast } from "@hyvor/design/components";
    import { languagesStore } from "../../../../lib/stores/languagesStore";
    import type { LinkAnalysisLink } from "../../../../lib/types";
	import LinkStatusTag from "./LinkStatusTag.svelte";
	import { getLanguageById } from "../../../../lib/actions/languageActions";
	import { blogStore } from "../../../../lib/stores/blogStore";
	import { IconArrowClockwise, IconEyeSlashFill, IconPencilFill } from "@hyvor/icons";
	import { callIgnoreLink, callLinkAnalysisApi } from "../linkAnalysisActions";
	import { createEventDispatcher } from "svelte";
	import { consoleUrlWithBlog } from "../../../../lib/consoleUrl";
    
    export let link: LinkAnalysisLink;

    let isRechecking = false;

    const language = getLanguageById(link.post_variant_language_id);
    const postEditUrl = consoleUrlWithBlog(`/posts/${link.post_id}`);

    const dispatch = createEventDispatcher();

    function handleRecheck() {
        isRechecking = true;

        callLinkAnalysisApi(link.post_variant_id, [link.url])
            .then(res => {
                dispatch('update', res[0]);
            })
            .catch(e => {
                toast.error(e.message || "Failed to recheck link.");
            })
            .finally(() => {
                isRechecking = false;
            });
    }

    function handleIgnore() {
        isRechecking = true;

        const newIgnore = !link.ignored;

        callIgnoreLink(
            link.post_variant_id,
            link.url,
            newIgnore
        ).then(() => {
            dispatch('update', {id: link.id, ignored: newIgnore});
        }).catch(e => {
            toast.error(e.message || "Failed to ignore link.");
        }).finally(() => {
            isRechecking = false;
        });

    }

</script>

<TableRow>

    <div>
        <div>
            {link.post_variant_title || '(No title)'}

            {#if $languagesStore.length > 1 && language}
                <div class="language-tag">
                    <Tag size="small">{language.code}</Tag>
                </div>
            {/if}
        </div>
    </div>

    <div class="link">
        <Link 
            href={link.full_url} 
            target="_blank" 
            className="link"
        >
            {link.url}
        </Link>
    </div>

    <div>
        {#if isRechecking}
            <Loader size="small" />
        {:else}
            <LinkStatusTag status={link.ignored ? -2 : link.status_code} />
        {/if}
    </div>

    <div class="actions">

        <Tooltip text="Edit in Editor">
            <IconButton
                as="a"
                href={postEditUrl}
                target="_blank"
                size="small"
                color="gray"
                variant="fill-light"
            >
                <IconPencilFill size={10} />
            </IconButton>
        </Tooltip>

        <Tooltip text="Recheck">
            <IconButton 
                on:click={handleRecheck}
                size="small"
                color="gray"
                variant="fill-light"
                disabled={isRechecking}
            >
                <IconArrowClockwise size={14} />
            </IconButton>
        </Tooltip>

        <Tooltip text="Ignore this link">
            <IconButton 
                on:click={handleIgnore}
                color={link.ignored ? "accent" : "gray"}
                variant="fill-light"
                size="small"
            >
                <IconEyeSlashFill size={14} />
            </IconButton>
        </Tooltip>

    </div>

</TableRow>

<style>
    .link {
        word-break: break-all;
    }
    .actions {
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .language-tag {
        margin-top: 2px;
    }
</style>