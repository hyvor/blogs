<script lang="ts">
	import { onMount } from 'svelte';
	import { buildDiffDoc, diffDoc, Editor } from '@hyvor/richtext';
	import type { Author, SuggestionSource, SuggestionSourceEntry } from '@hyvor/richtext';
	import { Node } from 'prosemirror-model';
	import { Modal, Button, Callout, Loader } from '@hyvor/design/components';
	import IconCheck from '@hyvor/icons/IconCheck';
	import { editorConfig, schema } from '../posts/[postId]/Body/Editor/editor';
	import { resolveAuthor } from '../posts/[postId]/Body/Editor/suggestions';
	import {
		DEFAULT_CONTENT_JSON,
		getCurrentDocumentForVariant,
		type CurrentDocument,
		type DocumentChange
	} from './agentApi';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		changes: DocumentChange[];
		initialPostVariantId?: number | null;
		applying?: boolean;
		onclose: () => void;
		onapply: (change: DocumentChange, finalContent: string) => Promise<void> | void;
	}

	let {
		changes,
		initialPostVariantId = null,
		applying = false,
		onclose,
		onapply
	}: Props = $props();

	let selectedPostVariantId = $state(initialPostVariantId ?? changes[0]?.postVariantId ?? null);
	let appliedIds: Set<number> = $state(new Set());
	let loadingCurrentDocuments = $state(true);
	// the post's actual current (content_unsaved) version/content, fetched fresh - used both to
	// diff against (instead of a blank document) and to detect if the post changed since the
	// agent suggested this change
	let currentByPost: Record<number, CurrentDocument> = $state({});

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
	function buildInitialContent(change: DocumentChange): string {
		const currentContent = currentByPost[change.postVariantId]?.content ?? DEFAULT_CONTENT_JSON;
		const oldNode = Node.fromJSON(schema, JSON.parse(currentContent));
		const newNode = Node.fromJSON(schema, JSON.parse(change.content));
		const diffs = diffDoc(oldNode, newNode);
		return JSON.stringify(buildDiffDoc(diffs, schema).doc.toJSON());
	}

	let contentByPost: Record<number, string> = $state({});
	let remainingByPost: Record<number, number> = $state({});

	onMount(async () => {
		const entries = await Promise.all(
			changes.map(async (change) => {
				try {
					const current = await getCurrentDocumentForVariant(change.postVariantId);
					return [change.postVariantId, current] as const;
				} catch {
					// couldn't load the current content (e.g. the post was deleted since) - fall
					// back to diffing against a blank document, with no staleness check
					return [change.postVariantId, null] as const;
				}
			})
		);
		currentByPost = Object.fromEntries(
			entries.filter((e): e is [number, CurrentDocument] => e[1] !== null)
		);

		contentByPost = Object.fromEntries(
			changes.map((change) => [change.postVariantId, buildInitialContent(change)])
		);
		remainingByPost = Object.fromEntries(
			Object.entries(contentByPost).map(([id, content]) => [
				id,
				countRemainingSuggestions(JSON.parse(content))
			])
		);
		loadingCurrentDocuments = false;
	});

	let remaining = $derived(
		selectedPostVariantId !== null ? (remainingByPost[selectedPostVariantId] ?? 0) : 0
	);
	let selectedApplied = $derived(
		selectedPostVariantId !== null && appliedIds.has(selectedPostVariantId)
	);
	// the post was edited again after the agent suggested this change - review carefully, since
	// the diff below is against the version the agent saw, not necessarily the latest
	let selectedIsStale = $derived.by(() => {
		if (selectedPostVariantId === null) return false;
		const change = changes.find((c) => c.postVariantId === selectedPostVariantId);
		const current = currentByPost[selectedPostVariantId];
		if (!change || change.version === undefined || !current) return false;
		return current.version > change.version;
	});

	function handleValueChange(value: string) {
		if (selectedPostVariantId === null) return;
		contentByPost[selectedPostVariantId] = value;
		remainingByPost[selectedPostVariantId] = countRemainingSuggestions(JSON.parse(value));
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
		if (selectedPostVariantId === null || remaining > 0 || applying || selectedApplied) return;

		const change = changes.find((c) => c.postVariantId === selectedPostVariantId);
		const content = contentByPost[selectedPostVariantId];
		if (!change || content === undefined) return;

		await onapply(change, content);
		appliedIds.add(selectedPostVariantId);
		appliedIds = new Set(appliedIds);

		const next = changes.find((c) => !appliedIds.has(c.postVariantId));
		if (next) {
			selectedPostVariantId = next.postVariantId;
		}
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
				{#if selectedApplied}
					{i18n.t('console.agent.applied')}
				{:else if remaining > 0}
					{remaining} suggestion{remaining === 1 ? '' : 's'} remaining
				{:else}
					{i18n.t('console.agent.allResolved')}
				{/if}
			</span>
		</div>
		{#if loadingCurrentDocuments}
			<div class="body loading">
				<Loader />
			</div>
		{:else}
			<div class="body">
				<div class="posts-sidebar">
					{#each changes as change (change.postVariantId)}
						<button
							type="button"
							class="post-item"
							class:active={selectedPostVariantId === change.postVariantId}
							onclick={() => (selectedPostVariantId = change.postVariantId)}
						>
							<span>Post #{change.postVariantId}</span>
							{#if appliedIds.has(change.postVariantId)}
								<IconCheck size={13} />
							{/if}
						</button>
					{/each}
				</div>
				<div class="editor">
					{#if selectedIsStale}
						<Callout type="warning">
							This post has been updated since the AI suggested this change. Please review the
							changes carefully before applying them.
						</Callout>
					{/if}
					{#if selectedPostVariantId !== null}
						{#key selectedPostVariantId}
							<Editor
								value={contentByPost[selectedPostVariantId]}
								{schema}
								editorConfig={diffEditorConfig}
								editable={!selectedApplied}
								onvaluechange={handleValueChange}
							/>
						{/key}
					{/if}
				</div>
			</div>
		{/if}
		<div class="footer">
			<Button color="input" onclick={onclose} disabled={applying}
				>{i18n.t('console.common.close')}</Button
			>
			<Button disabled={remaining > 0 || applying || selectedApplied} onclick={handleApply}>
				{#if selectedApplied}
					{i18n.t('console.agent.applied')}
				{:else if applying}
					Applying…
				{:else}
					{i18n.t('console.agent.applyChanges')}
				{/if}
			</Button>
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

	.posts-sidebar {
		width: 180px;
		flex-shrink: 0;
		overflow-y: auto;
		border-right: 1px solid var(--border);
		padding: 15px;
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	.post-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
		width: 100%;
		padding: 8px 10px;
		border-radius: 6px;
		border: none;
		background: none;
		font: inherit;
		font-size: 13px;
		color: var(--text);
		text-align: left;
		cursor: pointer;
	}

	.post-item:hover {
		background-color: var(--box-background);
	}

	.post-item.active {
		background-color: var(--accent-light-mid);
		color: var(--accent);
		font-weight: 600;
	}

	.post-item :global(svg) {
		flex-shrink: 0;
		color: var(--green);
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
