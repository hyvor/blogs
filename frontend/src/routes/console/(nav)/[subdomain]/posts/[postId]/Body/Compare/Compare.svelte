<script lang="ts">
	import { buildDiffDoc, diffDoc, Editor } from '@hyvor/richtext';
	import { editorConfig, schema } from '../Editor/editor';
	import { Node } from 'prosemirror-model';
	import { Modal, Switch } from '@hyvor/design/components';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	let showDiff = $state(true);

	interface Props {
		leftTitle?: string;
		rightTitle?: string;
		leftContent: string;
		rightContent: string;
		onclose: () => void;
	}
	let {
		leftTitle = i18n.t('console.postEditor.compare.publishedVersion'),
		rightTitle = i18n.t('console.postEditor.compare.editingVersion'),
		leftContent,
		rightContent,
		onclose
	}: Props = $props();

	const diffContent = $derived.by(() => {
		if (!showDiff) {
			return rightContent;
		}

		const publishedContent = Node.fromJSON(schema, JSON.parse(leftContent));
		const editingContent = Node.fromJSON(schema, JSON.parse(rightContent));

		const diff = diffDoc(publishedContent, editingContent);

		const doc = buildDiffDoc(diff, schema).doc.toJSON();
		return JSON.stringify(doc);
	});
</script>

<Modal bare width="1400px" height="calc(100% - 40px)" {onclose} show={true} appendToBody>
	<div class="inner">
		<div class="part left">
			<div class="header">{leftTitle}</div>
			<div class="editor">
				<Editor value={leftContent} {schema} {editorConfig} editable={false} />
			</div>
		</div>
		<div class="part">
			<div class="header">
				{rightTitle}
				<span class="switch">
					<Switch bind:checked={showDiff}>{i18n.t('console.postEditor.compare.showDiff')}</Switch>
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
		position: absolute;
		inset: 0;
		display: flex;
		overflow: hidden;
		border-radius: inherit;
	}

	.part {
		flex: 1;
		display: flex;
		flex-direction: column;
		height: 100%;
		min-height: 0;
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
		min-height: 0;
		overflow: auto;
		padding: 15px 30px;
	}

	.editor :global(.ProseMirror) {
		padding: 0 !important;
	}
</style>
