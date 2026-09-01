<script lang="ts">
	import {
		Button,
		InputGroup,
		Modal,
		Radio,
		SplitControl,
		Textarea,
		toast
	} from '@hyvor/design/components';
	import { clearBlogCache } from './dangerActions';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	const TYPE_KEYS = {
		template: 'console.settings.danger.cache.typeTemplate',
		paths: 'console.settings.danger.cache.typePaths',
		all: 'console.settings.danger.cache.typeAll'
	} as const;
	const T = i18n.T;

	interface Props {
		show?: boolean;
	}

	let { show = $bindable(false) }: Props = $props();

	let type: 'template' | 'paths' | 'all' = $state('template');
	let paths = $state('');

	function handleClick() {
		const toastId = toast.loading(i18n.t('console.settings.danger.cache.clearing'));
		show = false;

		clearBlogCache({
			type,
			paths: paths.split('\n').filter((path) => path.trim() !== '')
		})
			.then(() => {
				toast.success(i18n.t('console.settings.danger.cache.cleared'), { id: toastId });
			})
			.catch((err) => {
				toast.error(err.message, { id: toastId });
			});
	}
</script>

<Modal title={i18n.t('console.settings.danger.clearCache')} bind:show>
	<SplitControl label={i18n.t('console.tools.import.type')}>
		<InputGroup>
			<Radio bind:group={type} value="template"
				>{i18n.t('console.settings.danger.cache.typeTemplate')}</Radio
			>

			<Radio bind:group={type} value="paths"
				>{i18n.t('console.settings.danger.cache.typePaths')}</Radio
			>

			<Radio bind:group={type} value="all">{i18n.t('console.common.all')}</Radio>
		</InputGroup>

		<p>
			<T
				key="console.settings.danger.cache.typeExplain"
				params={{ strong: { element: 'strong' }, type: i18n.t(TYPE_KEYS[type]), which: type }}
			/>
		</p>
	</SplitControl>

	{#if type === 'paths'}
		<SplitControl
			label={i18n.t('console.settings.danger.cache.typePaths')}
			caption={i18n.t('console.settings.danger.cache.pathsCaption')}
		>
			<Textarea
				bind:value={paths}
				placeholder="/assets/script.js
/assets/style.css"
			/>
		</SplitControl>
	{/if}

	{#snippet footer()}
		<Button variant="invisible" on:click={() => (show = false)}
			>{i18n.t('console.common.cancel')}</Button
		>

		<Button on:click={handleClick}>{i18n.t('console.settings.danger.clearCache')}</Button>
	{/snippet}
</Modal>
