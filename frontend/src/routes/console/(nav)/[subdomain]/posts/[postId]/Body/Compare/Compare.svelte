<script lang="ts">
	import { buildDiffDoc, diffDoc, Editor } from '@hyvor/richtext';
	import { postVariantStore } from '../../../postStore';
	import { editorConfig, schema } from '../Editor/editor';
	import { Node } from 'prosemirror-model';
	import { Modal, Switch } from '@hyvor/design/components';

	let showDiff = $state(true);

	interface Props {
		onclose: () => void;
	}
	let { onclose } = $props();

	const diffContent = $derived.by(() => {
		const newContentJson = $postVariantStore.content_unsaved || $postVariantStore.content!;

		if (!showDiff) {
			return newContentJson;
		}

		const publishedContent = Node.fromJSON(schema, JSON.parse($postVariantStore.content!));
		const editingContent = Node.fromJSON(schema, JSON.parse(newContentJson));

		const diff = diffDoc(publishedContent, editingContent);

		const doc = buildDiffDoc(diff, schema).toJSON();
		return JSON.stringify(doc);
	});
</script>

<Modal bare width="1400px" height="calc(100% - 40px)" {onclose} show={true} appendToBody>
	<div class="inner">
		<div class="part left">
			<div class="header">Published Version</div>
			<div class="editor">
				<Editor value={$postVariantStore.content} {schema} {editorConfig} editable={false} />
			</div>
		</div>
		<div class="part">
			<div class="header">
				Editing Version
				<span class="switch">
					<Switch bind:checked={showDiff}>Show diff</Switch>
				</span>
			</div>
			<div class="editor">
				{#key showDiff}
					<Editor value={diffContent} {schema} {editorConfig} editable={false} />
				{/key}
			</div>
		</div>
	</div>
</Modal>

<style>
	.inner {
		display: flex;
	}

	.part {
		flex: 1;
		display: flex;
		flex-direction: column;
		height: 100%;
		overflow: hidden;
	}

	.part.left {
		border-right: 1px solid var(--border);
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

	.switch {
		font-weight: normal;
	}

	.editor {
		flex: 1;
		overflow: auto;
		padding: 15px 30px;
	}

	.editor :global(.ProseMirror) {
		padding: 0 !important;
	}
</style>
