<script>
	import IconBan from '@hyvor/icons/IconBan';
	import BlogStatus from './BlogStatus.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
</script>

<BlogStatus
	icon={IconBan}
	title={i18n.t('console.blogStatus.banned.title')}
	description={i18n.t('console.blogStatus.banned.description')}
/>
