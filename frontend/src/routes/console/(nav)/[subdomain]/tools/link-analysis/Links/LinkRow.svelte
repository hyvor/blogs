<script lang="ts">
	import {
		IconButton,
		Link,
		Loader,
		TableRow,
		Tag,
		Tooltip,
		toast
	} from '@hyvor/design/components';
	import { languagesStore } from '../../../../../lib/stores/languagesStore';
	import type { LinkAnalysisLink } from '../../../../../lib/types';
	import LinkStatusTag from './LinkStatusTag.svelte';
	import { getLanguageById } from '../../../../../lib/actions/languageActions';
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import IconEyeSlashFill from '@hyvor/icons/IconEyeSlashFill';
	import IconPencilFill from '@hyvor/icons/IconPencilFill';

	import { callIgnoreLink, callLinkAnalysisApi } from '../linkAnalysisActions';
	import { createEventDispatcher } from 'svelte';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		link: LinkAnalysisLink;
	}

	let { link }: Props = $props();

	let isRechecking = $state(false);

	const language = getLanguageById(link.post_variant_language_id);
	const postEditUrl = consoleUrlWithBlog(`/posts/${link.post_id}`);

	const dispatch = createEventDispatcher();

	function handleRecheck() {
		isRechecking = true;

		callLinkAnalysisApi(link.post_variant_id, [link.url])
			.then((res) => {
				dispatch('update', res[0]);
			})
			.catch((e) => {
				toast.error(e.message || i18n.t('console.tools.linkAnalysis.failedToRecheck'));
			})
			.finally(() => {
				isRechecking = false;
			});
	}

	function handleIgnore() {
		isRechecking = true;

		const newIgnore = !link.ignored;

		callIgnoreLink(link.post_variant_id, link.url, newIgnore)
			.then((link) => {
				dispatch('update', link);
			})
			.catch((e) => {
				toast.error(e.message || i18n.t('console.tools.linkAnalysis.failedToIgnore'));
			})
			.finally(() => {
				isRechecking = false;
			});
	}
</script>

<TableRow>
	<div>
		<a href={link.post_variant_url} target="_blank" class="post-url">
			{link.post_variant_title || i18n.t('console.tools.linkAnalysis.noTitle')}
		</a>
		{#if $languagesStore.length > 1 && language}
			<div class="language-tag">
				<Tag size="small">{language.code}</Tag>
			</div>
		{/if}
	</div>

	<div class="link">
		<Link href={link.full_url} target="_blank" className="link">
			{link.url}
		</Link>
	</div>

	<div title={link.comment}>
		{#if isRechecking}
			<Loader size="small" />
		{:else}
			<LinkStatusTag
				status={link.ignored ? -2 : link.status_code}
				ignoreReason={link.ignore_reason}
			/>
		{/if}
	</div>

	<div class="actions">
		<Tooltip text={i18n.t('console.tools.linkAnalysis.editInEditor')}>
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

		<Tooltip text={i18n.t('console.tools.linkAnalysis.recheck')}>
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

		<Tooltip text={i18n.t('console.tools.linkAnalysis.ignoreLink')}>
			<IconButton
				on:click={handleIgnore}
				color={link.ignored ? 'accent' : 'gray'}
				variant="fill-light"
				size="small"
				disabled={link.ignore_reason}
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
	.post-url:hover {
		text-decoration: underline;
	}
</style>
