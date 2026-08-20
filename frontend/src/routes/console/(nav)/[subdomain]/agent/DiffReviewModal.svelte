<script lang="ts">
	import { buildDiffDoc, diffDoc, Editor } from '@hyvor/richtext';
	import type { Author, SuggestionSource, SuggestionSourceEntry } from '@hyvor/richtext';
	import { Node } from 'prosemirror-model';
	import { Modal, Button } from '@hyvor/design/components';
	import { editorConfig, schema } from '../posts/[postId]/Body/Editor/editor';
	import { resolveAuthor } from '../posts/[postId]/Body/Editor/suggestions';

	interface Props {
		leftContent: string;
		rightContent: string;
		applying?: boolean;
		onclose: () => void;
		onfinish: (finalContent: string) => void | Promise<void>;
	}

	let { leftContent, rightContent, applying = false, onclose, onfinish }: Props = $props();

	// one-shot review session: everything shown here comes from a single agent-suggested diff, so
	// the source just needs to say "these are AI's" - nothing to persist to a backend suggestions API
	function createEphemeralSuggestionSource(): SuggestionSource {
		const local = new Map<string, SuggestionSourceEntry>();
		return {
			async get(ids) {
				const entries: Record<string, SuggestionSourceEntry | null> = {};
				for (const id of ids) {
					entries[id] = local.get(id) ?? { author: 'ai' as Author, timestamp: Date.now(), comments: [] };
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

	const initialContent = (() => {
		const oldNode = Node.fromJSON(schema, JSON.parse(leftContent));
		const newNode = Node.fromJSON(schema, JSON.parse(rightContent));
		const diffs = diffDoc(oldNode, newNode);
		return JSON.stringify(buildDiffDoc(diffs, schema).doc.toJSON());
	})();

	let remaining = $state(countRemainingSuggestions(JSON.parse(initialContent)));
	let currentContent = $state(initialContent);

	function handleValueChange(value: string) {
		currentContent = value;
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

	function handleFinish() {
		if (remaining > 0 || applying) return;
		onfinish(currentContent);
	}
</script>

<Modal bare width="900px" height="calc(100% - 40px)" {onclose} show={true} appendToBody id="diff-review-modal">
	<div class="inner">
		<div class="header">
			<span>Review suggested changes</span>
			<span class="remaining">
				{#if remaining > 0}
					{remaining} suggestion{remaining === 1 ? '' : 's'} remaining
				{:else}
					All suggestions resolved
				{/if}
			</span>
		</div>
		<div class="editor">
			<Editor
				value={initialContent}
				{schema}
				editorConfig={diffEditorConfig}
				editable={true}
				onvaluechange={handleValueChange}
			/>
		</div>
		<div class="footer">
			<Button color="input" onclick={onclose} disabled={applying}>Cancel</Button>
			<Button disabled={remaining > 0 || applying} onclick={handleFinish}>
				{applying ? 'Applying…' : 'Apply changes'}
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
	}

	.remaining {
		font-weight: normal;
		font-size: 12px;
		color: var(--text-light);
	}

	.editor {
		flex: 1;
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
	}
</style>
