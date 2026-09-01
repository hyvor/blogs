<script lang="ts">
	import { Link } from '@hyvor/design/components';
	import { postVariantStore } from '../../../../postStore';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { getI18n } from '../../../../../../../lib/i18n';

	const i18n = getI18n();
</script>

{i18n.t('console.postEditor.publishedToast.published')}
<Link href={$postVariantStore.url} target="_blank">
	<span>{i18n.t('console.postEditor.publishedToast.viewPost')}</span>
	{#snippet end()}
		<IconBoxArrowUpRight size={10} />
	{/snippet}
</Link>

<style>
	span {
		font-size: 14px;
	}
</style>
