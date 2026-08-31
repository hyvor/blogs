<script lang="ts">
	import type { Language } from '../../../../lib/types';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	interface Props {
		language: Language;
	}

	let { language }: Props = $props();
</script>

<T
	key="console.settings.languages.deleteContent"
	params={{
		strong: { element: 'strong' },
		language: language.name,
		code: language.code
	}}
/>

<ul>
	<li>
		<T
			key="console.settings.languages.deleteScope.blog"
			params={{ strong: { element: 'strong' } }}
		/>
	</li>
	<li><strong>{i18n.t('console.nav.posts')}</strong></li>
	<li><strong>{i18n.t('console.settings.nav.users')}</strong></li>
	<li><strong>{i18n.t('console.settings.nav.tags')}</strong></li>
	<li><strong>{i18n.t('console.settings.languages.deleteScope.navigations')}</strong></li>
</ul>
