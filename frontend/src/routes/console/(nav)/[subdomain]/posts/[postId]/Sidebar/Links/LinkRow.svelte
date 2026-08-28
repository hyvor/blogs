<script lang="ts">
	import IconEyeSlashFill from '@hyvor/icons/IconEyeSlashFill';
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import { postEditor, postVariantStore, updatePostVariantStore } from '../../../postStore';
	import { Tooltip, toast } from '@hyvor/design/components';
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
	import { IconButton } from '@hyvor/design/components';
	import LinkStatusTag from '../../../../tools/link-analysis/Links/LinkStatusTag.svelte';
	import { focusLinkInEditor, isHttpLink, LINK_STATUS } from '../../../../../../lib/links/links';
	import { Loader } from '@hyvor/design/components';
	import { getStatusType, type Link } from '../../../../../../lib/links/links';
	import { linksStore, variantLinkAnalysisStore } from './linksStore';
	import {
		callIgnoreLink,
		callLinkAnalysisApi
	} from '../../../../tools/link-analysis/linkAnalysisActions';

	let isReloading = $state(false);

	function handleReload() {
		isReloading = true;

		callLinkAnalysisApi($postVariantStore.id, [link.originalHref])
			.then((res) => {
				let status = res.find((l) => l.url === link.originalHref)?.status_code;
				if (status === undefined) {
					status = LINK_STATUS.ERROR;
				}

				updatePostVariantStore({
					link_analysis: {
						...$postVariantStore.link_analysis,
						[link.originalHref]: status
					}
				});
			})
			.catch(() => {
				updatePostVariantStore({
					link_analysis: {
						...$postVariantStore.link_analysis,
						[link.originalHref]: LINK_STATUS.ERROR
					}
				});
			})
			.finally(() => {
				isReloading = false;
			});
	}

	function handleIgnore() {
		isReloading = true;

		const status = linkStatusType === 'ignored' ? false : true;

		callIgnoreLink($postVariantStore.id, link.originalHref, status)
			.then((res) => {
				updatePostVariantStore({
					link_analysis: {
						...$postVariantStore.link_analysis,
						[link.originalHref]: status ? LINK_STATUS.IGNORED : res.status_code
					}
				});
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				isReloading = false;
			});
	}

	interface Props {
		link: Link;
	}

	let { link }: Props = $props();
	let linkStatus = $derived.by(() => {
		let status = $variantLinkAnalysisStore[link.originalHref];
		if (status === undefined) {
			return LINK_STATUS.ERROR;
		}
		return status;
	});

	let linkStatusType = $derived(getStatusType(linkStatus));
	let isHttp = $derived(isHttpLink(link));

	let linkObject = $derived($linksStore.find((l) => l.url === link.originalHref));
</script>

<div class="link-wrap type-{linkStatusType}">
	<div class="link-name">
		<div class="link-anchor">
			{link.anchor}
		</div>

		<div class="link-url">
			<a href={link.href} target="_blank" rel="nofollow">
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
				ignoreReason={linkObject?.ignore_reason}
			/>
		{/if}
	</div>

	<div class="link-buttons">
		<Tooltip text="Edit in Editor">
			<IconButton
				size={22}
				color="input"
				on:click={() => focusLinkInEditor(link, $postEditor.getView())}
			>
				<IconPencilFill size={12} />
			</IconButton>
		</Tooltip>

		<Tooltip text="Recheck">
			<IconButton size={22} color="input" on:click={handleReload} disabled={!isHttp || isReloading}>
				<IconArrowClockwise size={12} />
			</IconButton>
		</Tooltip>

		<Tooltip text={(linkStatusType === 'ignored' ? 'Unignore' : 'Ignore') + ' this link'}>
			<IconButton
				size={22}
				color={linkStatusType === 'ignored' ? 'accent' : 'input'}
				on:click={handleIgnore}
				disabled={!isHttp || isReloading || linkObject?.ignore_reason}
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
