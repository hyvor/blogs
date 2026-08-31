<script lang="ts">
	import { Callout, Link, SplitControl, Switch, Text } from '@hyvor/design/components';
	import { blogStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import CodemirrorEditor from '../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	function handleFlashloadChange(e: any) {
		updateBlogStore({ flashload: e.target.checked });
	}
</script>

<BlogSettingsSave keys={['code_head', 'code_foot', 'flashload']} />

<div class="settings">
	<div class="intro">
		<Callout type="info">
			<T
				key="console.settings.code.intro"
				params={{
					strong: { element: 'strong' },
					docsLink: {
						element: 'a',
						props: {
							href: 'https://blogs.hyvor.com/docs/custom-code',
							target: '_blank',
							class: 'hds-link'
						}
					},
					twigLink: {
						element: 'a',
						props: { href: '/docs/themes-templates#twig', target: '_blank', class: 'hds-link' }
					},
					variablesLink: {
						element: 'a',
						props: { href: '/docs/themes-templates#variables', target: '_blank', class: 'hds-link' }
					}
				}}
			/>
		</Callout>
	</div>

	<div class="code">
		<SplitControl label={i18n.t('console.settings.code.headCode')}>
			{#snippet caption()}
				<div>{i18n.t('console.settings.code.headCodeCaption')}</div>
			{/snippet}

			<CodemirrorEditor
				value={$blogStore.code_head || ''}
				id="comments"
				ext="twig"
				onchange={(value) => updateBlogStore({ code_head: value })}
			/>
		</SplitControl>

		<SplitControl label={i18n.t('console.settings.code.footCode')}>
			{#snippet caption()}
				<div>{i18n.t('console.settings.code.footCodeCaption')}</div>
			{/snippet}

			<CodemirrorEditor
				value={$blogStore.code_foot || ''}
				id="newsletter"
				ext="twig"
				onchange={(value) => updateBlogStore({ code_foot: value })}
			/>
		</SplitControl>
	</div>

	<SplitControl label={i18n.t('console.settings.code.flashload')}>
		{#snippet caption()}
			<div>
				<T
					key="console.settings.code.flashloadCaption"
					params={{
						link: {
							element: 'a',
							props: {
								href: 'https://github.com/hyvor/flashload',
								target: '_blank',
								class: 'hds-link'
							}
						}
					}}
				/>
			</div>
		{/snippet}

		<Switch checked={$blogStore.flashload} on:change={handleFlashloadChange} />
	</SplitControl>
</div>

<style>
	.settings {
		flex: 1;
		overflow: auto;
		padding: 25px 30px;
	}
	.settings :global(.split-control) {
		flex-direction: column;
	}

	.settings :global(.CodeMirror) {
		min-height: 200px;
	}
	.intro {
		padding: 15px;
	}
</style>
