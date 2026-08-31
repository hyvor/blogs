<script lang="ts">
	import { Link, SplitControl, Text } from '@hyvor/design/components';
	import { blogStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import CodemirrorEditor from '../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import { consoleUrlWithBlog } from '../../../../lib/consoleUrl';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;
</script>

<BlogSettingsSave keys={['comments_code', 'newsletter_code']} />

<div class="settings">
	<SplitControl label={i18n.t('console.settings.comments.commentsCode')}>
		{#snippet caption()}
			<div>
				<T
					key="console.settings.comments.commentsCodeCaption"
					params={{
						varsLink: {
							element: 'a',
							props: {
								href: '/docs/themes-templates#variables',
								target: '_blank',
								class: 'hds-link'
							}
						},
						integrationsLink: {
							element: 'a',
							props: {
								href: consoleUrlWithBlog('/integrations/hyvor-talk'),
								class: 'hds-link'
							}
						}
					}}
				/>
			</div>
		{/snippet}

		<CodemirrorEditor
			value={$blogStore.comments_code || ''}
			id="comments"
			ext="twig"
			on:change={(e) => updateBlogStore({ comments_code: e.detail })}
		/>

		<div style="margin-top:10px;">
			<Text light small>
				{i18n.t('console.settings.comments.commentsCodeNote')}
			</Text>
		</div>
	</SplitControl>

	<SplitControl label={i18n.t('console.settings.comments.newsletterCode')}>
		{#snippet caption()}
			<div>
				<T
					key="console.settings.comments.newsletterCodeCaption"
					params={{
						varsLink: {
							element: 'a',
							props: {
								href: '/docs/themes-templates#variables',
								target: '_blank',
								class: 'hds-link'
							}
						},
						integrationsLink: {
							element: 'a',
							props: {
								href: consoleUrlWithBlog('/integrations/hyvor-talk'),
								class: 'hds-link'
							}
						}
					}}
				/>
			</div>
		{/snippet}

		<CodemirrorEditor
			value={$blogStore.newsletter_code || ''}
			id="newsletter"
			ext="twig"
			on:change={(e) => updateBlogStore({ newsletter_code: e.detail })}
		/>

		<div style="margin-top:10px;">
			<Text light small>
				{i18n.t('console.settings.comments.newsletterCodeNote')}
			</Text>
		</div>
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
</style>
