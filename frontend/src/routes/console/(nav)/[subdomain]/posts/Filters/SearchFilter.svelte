<script lang="ts">
	import { TextInput } from '@hyvor/design/components';
	import { postListFiltersStore, setFilter } from '../postListStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	let value = $state($postListFiltersStore.search);

	let timeout: null | ReturnType<typeof setTimeout> = null;

	function handleInput(e: any) {
		if (timeout) clearTimeout(timeout);
		timeout = setTimeout(() => {
			setFilter('search', e.target.value);
		}, 500);
	}
</script>

<TextInput bind:value on:input={handleInput} placeholder={i18n.t('console.common.searchPlaceholder')} />
