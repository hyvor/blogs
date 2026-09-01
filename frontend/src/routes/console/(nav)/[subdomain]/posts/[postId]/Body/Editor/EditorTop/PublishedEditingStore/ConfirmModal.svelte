<script lang="ts">
	import { getI18n } from '../../../../../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	interface Props {
		words?: number;
	}

	let { words = 0 }: Props = $props();
</script>

{i18n.t('console.postEditor.publishedEditing.confirmIntro')}

<ul>
	<li>
		<T
			key="console.postEditor.publishedEditing.confirmChangedWords"
			params={{ words, strong: { element: 'strong' } }}
		/>
	</li>
	<li>{i18n.t('console.postEditor.publishedEditing.confirmIrreversible')}</li>
	<li>{i18n.t('console.postEditor.publishedEditing.confirmRevert')}</li>
</ul>
