<script lang="ts">
	import {
		Callout,
		Caption,
		FormControl,
		Link,
		Radio,
		SplitControl,
		Switch
	} from '@hyvor/design/components';
	import { blogStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import CodemirrorEditor from '../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	function handleAllowIndexingChange(e: any) {
		updateBlogStore({ seo_indexing: e.target.checked });
	}

	function handleExternalLinksFollowChange(e: any, value: 'follow' | 'nofollow') {
		updateBlogStore({ seo_external_links_follow: value });
	}

	function handleRichSchemaChange(e: any) {
		updateBlogStore({ seo_rich_schema: e.target.checked });
	}
</script>

<BlogSettingsSave
	keys={['seo_indexing', 'seo_external_links_follow', 'seo_rich_schema', 'seo_robots_txt']}
/>

<div class="settings">
	<SplitControl
		label={i18n.t('console.settings.seo.allowIndexing')}
		caption={i18n.t('console.settings.seo.allowIndexingCaption')}
	>
		<Switch checked={$blogStore.seo_indexing} on:change={handleAllowIndexingChange} />
	</SplitControl>

	<SplitControl
		label={i18n.t('console.settings.seo.externalLinks')}
		caption={i18n.t('console.settings.seo.externalLinksCaption')}
	>
		<FormControl>
			<Radio
				value="follow"
				group={$blogStore.seo_external_links_follow}
				on:change={(e) => handleExternalLinksFollowChange(e, 'follow')}
			>
				{i18n.t('console.settings.seo.follow')}
			</Radio>

			<Radio
				value="nofollow"
				group={$blogStore.seo_external_links_follow}
				on:change={(e) => handleExternalLinksFollowChange(e, 'nofollow')}
			>
				{i18n.t('console.settings.seo.noFollow')}
			</Radio>
		</FormControl>
	</SplitControl>

	<SplitControl label={i18n.t('console.settings.seo.richSchema')}>
		{#snippet caption()}
			<Caption>
				<T
					key="console.settings.seo.richSchemaCaption"
					params={{
						link: {
							element: 'a',
							props: { href: '/docs/seo#rich-schema', target: '_blank', class: 'hds-link' }
						}
					}}
				/>
			</Caption>
		{/snippet}

		<Switch checked={$blogStore.seo_rich_schema} on:change={handleRichSchemaChange} />
	</SplitControl>

	<div class="robots-split">
		<SplitControl
			label={i18n.t('console.settings.seo.robotsTxt')}
			caption={i18n.t('console.settings.seo.robotsTxtCaption')}
		>
			<CodemirrorEditor
				value={$blogStore.seo_robots_txt || ''}
				id="robots_txt"
				ext="twig"
				onchange={(value) => updateBlogStore({ seo_robots_txt: value })}
			/>
		</SplitControl>
	</div>
</div>

<style>
	.robots-split :global(.split-control) {
		flex-direction: column;
	}
	.robots-split :global(.CodeMirror) {
		min-height: 150px;
	}
	.settings {
		flex: 1;
		overflow: auto;
		padding: 25px 30px;
	}
</style>
