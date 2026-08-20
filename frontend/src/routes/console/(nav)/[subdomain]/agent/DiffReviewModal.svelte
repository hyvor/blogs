<script lang="ts">
	import { buildDiffDoc, diffDoc, Editor } from '@hyvor/richtext';
	import type { Author, SuggestionSource, SuggestionSourceEntry } from '@hyvor/richtext';
	import { Node } from 'prosemirror-model';
	import { Modal, Button } from '@hyvor/design/components';
	import IconCheck from '@hyvor/icons/IconCheck';
	import { editorConfig, schema } from '../posts/[postId]/Body/Editor/editor';
	import { resolveAuthor } from '../posts/[postId]/Body/Editor/suggestions';
	import { DEFAULT_CONTENT_JSON, type DocumentChange } from './agentApi';

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

	// we don't have the post's content as it was before the agent's edits (only the final
	// result comes over the wire) - so every post is diffed against a blank document for now,
	// which shows the suggested content as a full set of additions to review
	function buildInitialContent(change: DocumentChange): string {
		const oldNode = Node.fromJSON(schema, JSON.parse(DEFAULT_CONTENT_JSON));
		const newNode = Node.fromJSON(schema, JSON.parse(change.content));
		const diffs = diffDoc(oldNode, newNode);
		return JSON.stringify(buildDiffDoc(diffs, schema).doc.toJSON());
	}

	let contentByPost: Record<number, string> = $state(
		Object.fromEntries(changes.map((change) => [change.postVariantId, buildInitialContent(change)]))
	);
	let remainingByPost: Record<number, number> = $state(
		Object.fromEntries(
			Object.entries(contentByPost).map(([id, content]) => [
				id,
				countRemainingSuggestions(JSON.parse(content))
			])
		)
	);

	let remaining = $derived(
		selectedPostVariantId !== null ? (remainingByPost[selectedPostVariantId] ?? 0) : 0
	);
	let selectedApplied = $derived(
		selectedPostVariantId !== null && appliedIds.has(selectedPostVariantId)
	);

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
					Applied
				{:else if remaining > 0}
					{remaining} suggestion{remaining === 1 ? '' : 's'} remaining
				{:else}
					All suggestions resolved
				{/if}
			</span>
		</div>
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
		<div class="footer">
			<Button color="input" onclick={onclose} disabled={applying}>Close</Button>
			<Button disabled={remaining > 0 || applying || selectedApplied} onclick={handleApply}>
				{#if selectedApplied}
					Applied
				{:else if applying}
					Applying…
				{:else}
					Apply changes
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
