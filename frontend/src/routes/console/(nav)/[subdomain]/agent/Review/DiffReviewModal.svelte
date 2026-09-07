<script lang="ts">
	import { onMount } from 'svelte';
	import { buildDiffDoc, diffDoc, Editor } from '@hyvor/richtext';
	import type {
		Author,
		EditorConfig,
		SuggestionSource,
		SuggestionSourceEntry
	} from '@hyvor/richtext';
	import { Node } from 'prosemirror-model';
	import { Modal, Button, Callout, Tooltip, toast, confirm } from '@hyvor/design/components';
	import { editorConfig, schema } from '../../posts/[postId]/Body/Editor/editor';
	import {
		createEphemeralSuggestionSource,
		resolveAuthor
	} from '../../posts/[postId]/Body/Editor/suggestions';
	import { DEFAULT_CONTENT_JSON, type DocumentChange } from '../agentApi';
	import { getI18n } from '../../../../lib/i18n';
	import { getDocumentForPost } from '../../posts/[postId]/documentActions';
	import { applyDocumentChanges } from './diffActions';

	const i18n = getI18n();

	interface Props {
		change: DocumentChange;
		onclose: () => void;
		onapply: (eventId: number) => void;
	}

	let { change, onclose, onapply }: Props = $props();

	let applying = $state(false);
	let applied = $state(false);
	let loadingDocument = $state(true);

	// the post's actual current_unsaved and version
	// used to diff, and check if the post has changed since the agent edited
	let currentDocument: {
		version: number;
		content: string | null;
	} | null = $state(null);

	// @hyvor/richtext only publicly exports the Editor component + types (see its package.json
	// "exports"), not the internal getSuggestions()/acceptAllSuggestions() commands the built-in
	// review panel uses - so "how many suggestions are left" is derived here from the doc JSON
	// itself: a "suggestion" mark's attrs.id, or a node-level attrs.suggestions[].id.
	function countRemainingSuggestions(doc: any): number {
		const ids = new Set<string>();

		function walk(node: any) {
			if (!node) return;

			for (const mark of node.marks ?? []) {
				if (mark.type === 'suggestion' && mark.attrs?.id) {
					ids.add(mark.attrs.id);
				}
			}

			for (const suggestion of node.attrs?.suggestions ?? []) {
				if (suggestion?.id) ids.add(suggestion.id);
			}

			for (const child of node.content ?? []) {
				walk(child);
			}
		}

		walk(doc);
		return ids.size;
	}

	// diffs the agent's suggested content against the post's actual current content (fetched on
	// mount), falling back to a blank document if the current content couldn't be loaded - in
	// which case the suggested content shows as a full set of additions to review
	function buildInitialContent(): string {
		const currentContent = currentDocument?.content ?? DEFAULT_CONTENT_JSON;
		const oldNode = Node.fromJSON(schema, JSON.parse(currentContent));
		const newNode = Node.fromJSON(schema, JSON.parse(change.content));
		const diffs = diffDoc(oldNode, newNode);
		return JSON.stringify(buildDiffDoc(diffs, schema).doc.toJSON());
	}

	let content = $state('');
	let remaining = $state(0);

	// the post was edited again after the agent suggested this change - review carefully, since
	// the diff below is against the version the agent saw, not necessarily the latest
	let isStale = $derived.by(() => {
		const changeVersion = change.version;
		if (changeVersion === undefined || currentDocument === null) return false;
		return currentDocument.version > changeVersion;
	});

	function handleValueChange(value: string) {
		content = value;
		remaining = countRemainingSuggestions(JSON.parse(value));
	}

	const diffEditorConfig = {
		...editorConfig(),
		suggestions: {
			author: 'ai' as Author,
			mode: 'editing' as const,
			resolveAuthor,
			source: createEphemeralSuggestionSource({
				author: 'ai'
			}),
			disableCommenting: true
		}
	} as EditorConfig;

	function handleApply(force: boolean = false) {
		if (remaining > 0 || applying || applied) return;

		applying = true;

		applyDocumentChanges(change.eventId, content, currentDocument?.version ?? 0, force)
			.then((res) => {
				onapply(change.eventId);
			})
			.catch((e) => {
				if (e.code === 409) {
					handleForceApply();
				} else {
					toast.error(e.message || i18n.t('console.agent.review.applyFailed'));
				}
			})
			.finally(() => {
				applying = false;
			});
	}

	async function handleForceApply() {
		const confirmed = await confirm({
			title: i18n.t('console.agent.review.forceApplyTitle'),
			content: i18n.t('console.agent.review.forceApplyContent'),
			danger: true,
			autoClose: false
		});

		if (!confirmed) return;

		handleApply(true);
		confirmed.close();
	}

	onMount(async () => {
		getDocumentForPost({
			post_variant_id: change.postVariant.id
		})
			.then((res) => {
				currentDocument = {
					version: res.document.checkpoint_version,
					content: res.document.checkpoint_content || DEFAULT_CONTENT_JSON
				};
				content = buildInitialContent();
				remaining = countRemainingSuggestions(JSON.parse(content));
				loadingDocument = false;
			})
			.catch((error) => {
				toast.error(error.message || i18n.t('console.agent.review.loadFailed'));
			});
	});
</script>

<Modal
	bare
	width="calc(100% - 40px)"
	height="calc(100% - 40px)"
	{onclose}
	show={true}
	appendToBody
	loading={loadingDocument}
	id="diff-review-modal"
	closeOnEscape={false}
>
	{#if currentDocument}
		<div class="inner">
			<div class="header">
				<span>{i18n.t('console.agent.review.title')}</span>
				<span class="remaining">
					{#if applied}
						{i18n.t('console.agent.applied')}
					{:else if remaining > 0}
						{i18n.t('console.agent.review.suggestionsRemaining', { count: remaining })}
					{:else}
						{i18n.t('console.agent.allResolved')}
					{/if}
				</span>
			</div>
			<div class="body">
				<div class="editor">
					{#if isStale}
						<Callout type="warning">
							{i18n.t('console.agent.review.staleWarning')}
						</Callout>
					{/if}
					<Editor
						value={content}
						{schema}
						editorConfig={diffEditorConfig}
						editable={!applied}
						onvaluechange={handleValueChange}
					/>
				</div>
			</div>
			<div class="footer">
				<Button color="input" onclick={onclose} disabled={applying}
					>{i18n.t('console.common.close')}</Button
				>
				<Tooltip
					text={remaining > 0 ? i18n.t('console.agent.review.resolveRemaining') : ''}
					disabled={remaining === 0}
				>
					<Button
						disabled={remaining > 0 || applying || applied}
						onclick={() => handleApply()}
						loading={applying}
					>
						{#if applied}
							{i18n.t('console.agent.applied')}
						{:else if applying}
							{i18n.t('console.agent.review.applying')}
						{:else}
							{i18n.t('console.agent.applyChanges')}
						{/if}
					</Button>
				</Tooltip>
			</div>
		</div>
	{/if}
</Modal>

<style>
	:global(#diff-review-modal-desc) {
		height: 100%;
	}

	.inner {
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.header {
		padding: 15px 30px;
		font-weight: bold;
		font-size: 14px;
		border-bottom: 1px solid var(--border);
		display: flex;
		justify-content: space-between;
		align-items: center;
		height: 40px;
		flex-shrink: 0;
	}

	.remaining {
		font-weight: normal;
		font-size: 12px;
		color: var(--text-light);
	}

	.body {
		flex: 1;
		min-height: 0;
		display: flex;
	}

	.editor {
		flex: 1;
		min-width: 0;
		overflow: auto;
		padding: 15px 30px;
	}

	.footer {
		padding: 12px 30px;
		border-top: 1px solid var(--border);
		display: flex;
		justify-content: flex-end;
		gap: 10px;
		flex-shrink: 0;
	}
</style>
