<script lang="ts">
	import { buildDiffDoc, diffDoc, Editor } from '@hyvor/richtext';
	import { postVariantStore } from '../../../postStore';
	import { editorConfig, schema } from '../Editor/editor';
	import { Node } from 'prosemirror-model';

	const diffContent = $derived.by(() => {
		const publishedContent = Node.fromJSON(schema, JSON.parse($postVariantStore.content!));
		const editingContent = Node.fromJSON(
			schema,
			JSON.parse($postVariantStore.content_unsaved || $postVariantStore.content!)
		);

		const diff = diffDoc(publishedContent, editingContent);

		const doc = buildDiffDoc(diff, schema).toJSON();
		return JSON.stringify(doc);
	});
</script>

<div class="wrap">
	<div class="inner hds-box">
		<Editor value={diffContent} {schema} {editorConfig} />
	</div>
</div>

<style>
	.wrap {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
		z-index: 10000;
		background: rgba(0, 0, 0, 0.1);
	}

	.inner {
		width: 900px;
		max-width: 100%;
		height: calc(100% - 40px);
	}
</style>
