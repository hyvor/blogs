<script lang="ts">
	import { Callout, Link, SplitControl, Text } from '@hyvor/design/components';
	import { blogStore, integrationsStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import CodemirrorEditor from '../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import { consoleUrlWithBlog } from '../../../../lib/consoleUrl';
	import { getI18n } from '../../../../lib/i18n';
	import { onMount } from 'svelte';
	import { redirectIfCant } from '../../../../lib/scope.svelte';

	onMount(() => {
		redirectIfCant('blog.write');
	});

	const i18n = getI18n();
	const T = i18n.T;
</script>

<BlogSettingsSave keys={['comments_code', 'newsletter_code']} />

<div class="settings">
	<SplitControl label={i18n.t('console.settings.comments.commentsCode')}>
		{#snippet caption()}
			<div>
				<T
					key="console.settings.comments.commentsCodeIntro"
					params={{
						varsLink: {
							element: 'a',
							props: {
								href: '/docs/themes-templates#variables',
								target: '_blank',
								class: 'hds-link'
							}
						}
					}}
				/>

				{#if !$integrationsStore.hyvor_talk}
					<T
						key="console.settings.comments.connectTalkHint"
						params={{
							link: {
								element: 'a',
								props: {
									href: consoleUrlWithBlog('/integrations/hyvor-talk'),
									class: 'hds-link'
								}
							}
						}}
					/>
				{/if}
			</div>
		{/snippet}

		{#if $integrationsStore.hyvor_talk}
			<Callout type="info">
				{#snippet icon()}
					<img src="/img/services/hyvor-talk.svg" alt="Hyvor Talk Logo" width="18" />
				{/snippet}
				{#snippet title()}
					{i18n.t('console.settings.comments.talkEnabled')}
				{/snippet}
				<T
					key="console.settings.comments.talkEnabledBody"
					params={{ strong: { element: 'strong' } }}
				/>
			</Callout>
			<br />
		{/if}

		<CodemirrorEditor
			value={$blogStore.comments_code || ''}
			id="comments"
			ext="twig"
			onchange={(value) => updateBlogStore({ comments_code: value })}
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
					key="console.settings.comments.newsletterCodeIntro"
					params={{
						varsLink: {
							element: 'a',
							props: {
								href: '/docs/themes-templates#variables',
								target: '_blank',
								class: 'hds-link'
							}
						}
					}}
				/>

				{#if !$integrationsStore.hyvor_post}
					<T
						key="console.settings.comments.connectPostHint"
						params={{
							link: {
								element: 'a',
								props: {
									href: consoleUrlWithBlog('/integrations/hyvor-post'),
									class: 'hds-link'
								}
							}
						}}
					/>
				{/if}
			</div>
		{/snippet}

		{#if $integrationsStore.hyvor_post}
			<Callout type="info">
				{#snippet icon()}
					<img src="/img/services/hyvor-post.svg" alt="Hyvor Post Logo" width="18" />
				{/snippet}
				{#snippet title()}
					{i18n.t('console.settings.comments.postEnabled')}
				{/snippet}
				<T
					key="console.settings.comments.postEnabledBody"
					params={{ strong: { element: 'strong' } }}
				/>
			</Callout>
			<br />
		{/if}

		<CodemirrorEditor
			value={$blogStore.newsletter_code || ''}
			id="newsletter"
			ext="twig"
			onchange={(value) => updateBlogStore({ newsletter_code: value })}
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
