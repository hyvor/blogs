<script lang="ts">
	import { onMount } from 'svelte';
	import { buildDiffDoc, diffDoc, Editor } from '@hyvor/richtext';
	import type { Author, SuggestionSource, SuggestionSourceEntry } from '@hyvor/richtext';
	import { Node } from 'prosemirror-model';
	import { Modal, Button, Callout, Loader, Tooltip } from '@hyvor/design/components';
	import { editorConfig, schema } from '../../posts/[postId]/Body/Editor/editor';
	import { resolveAuthor } from '../../posts/[postId]/Body/Editor/suggestions';
	import {
		DEFAULT_CONTENT_JSON,
		getCurrentDocumentForVariant,
		type CurrentDocument,
		type DocumentChange
	} from '../agentApi';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		change: DocumentChange;
		onclose: () => void;
	}

	let { change, onclose }: Props = $props();

	let applying = $state(false);
	let applied = $state(false);
	let loadingCurrentDocument = $state(true);
	// the post's actual current (content_unsaved) version/content, fetched fresh - used both to
	// diff against (instead of a blank document) and to detect if the post changed since the
	// agent suggested this change
	let currentDocument: CurrentDocument | null = $state(null);

	// one-shot review session: everything shown here comes from a single agent-suggested diff, so
	// the source just needs to say "these are AI's" - nothing to persist to a backend suggestions API
	function createEphemeralSuggestionSource(): SuggestionSource {
		const local = new Map<string, SuggestionSourceEntry>();
		return {
			async get(ids) {
				const entries: Record<string, SuggestionSourceEntry | null> = {};
				for (const id of ids) {
					entries[id] = local.get(id) ?? {
						author: 'ai' as Author,
						timestamp: Date.now(),
						comments: []
					};
				}
				return entries;
			},
			create(id, _type, author, timestamp) {
				local.set(id, { author, timestamp, comments: [] });
			},
			reply(id, reply) {
				local.get(id)?.comments.push(reply);
			},
			resolve() {
				// ephemeral - resolution just lives in the doc itself (mark removed/text applied)
			}
		};
	}

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

	onMount(async () => {
		try {
			currentDocument = await getCurrentDocumentForVariant(change.postVariantId);
		} catch {
			// couldn't load the current content (e.g. the post was deleted since) - fall back to
			// diffing against a blank document, with no staleness check
			currentDocument = null;
		}

		content = buildInitialContent();
		remaining = countRemainingSuggestions(JSON.parse(content));
		loadingCurrentDocument = false;
	});

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
		...editorConfig,
		suggestions: {
			author: 'ai' as Author,
			mode: 'editing' as const,
			resolveAuthor,
			source: createEphemeralSuggestionSource()
		}
	};

	async function handleApply() {
		if (remaining > 0 || applying || applied) return;

		await onapply(change, content, currentDocument?.document_version ?? 0);
		applied = true;
	}
</script>

<Modal
	bare
	width="calc(100% - 40px)"
	height="calc(100% - 40px)"
	{onclose}
	show={true}
	appendToBody
	id="diff-review-modal"
>
	<div class="inner">
		<div class="header">
			<span>Review suggested changes</span>
			<span class="remaining">
				{#if applied}
					{i18n.t('console.agent.applied')}
				{:else if remaining > 0}
					{remaining} suggestion{remaining === 1 ? '' : 's'} remaining
				{:else}
					{i18n.t('console.agent.allResolved')}
				{/if}
			</span>
		</div>
		{#if loadingCurrentDocument}
			<div class="body loading">
				<Loader />
			</div>
		{:else}
			<div class="body">
				<div class="editor">
					{#if isStale}
						<Callout type="warning">
							This post has been updated since the AI suggested this change. Please review the
							changes carefully before applying them.
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
		{/if}
		<div class="footer">
			<Button color="input" onclick={onclose} disabled={applying}
				>{i18n.t('console.common.close')}</Button
			>
			<Tooltip
				text={remaining > 0 ? 'Resolve remaining suggestions' : ''}
				disabled={remaining === 0}
			>
				<Button disabled={remaining > 0 || applying || applied} onclick={handleApply}>
					{#if applied}
						{i18n.t('console.agent.applied')}
					{:else if applying}
						Applying…
					{:else}
						{i18n.t('console.agent.applyChanges')}
					{/if}
				</Button>
			</Tooltip>
		</div>
	</div>
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

	.body.loading {
		align-items: center;
		justify-content: center;
	}

	.editor {
		flex: 1;
		min-width: 0;
		overflow: auto;
		padding: 15px 30px;
	}

	.editor :global(.ProseMirror) {
		padding: 0 !important;
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
