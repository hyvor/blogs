<script lang="ts">
	import { onMount } from 'svelte';
	import { SplitControl, TextInput } from '@hyvor/design/components';
	import BlogSettingsSave from './BlogSettingsSave.svelte';
	import type { Blog } from '../../../lib/types';
	import { blogStore, updateBlogStoreVariantValue } from '../../../lib/stores/blogStore';
	import VariantInput from './@components/VariantInput/VariantInput.svelte';
	import ImageSetting from './@components/ImageSetting.svelte';
	import { getI18n } from '../../../lib/i18n';
	import { redirectIfCant } from '../../../lib/scope.svelte';

	const i18n = getI18n();

	onMount(() => {
		redirectIfCant('blog.write');
	});

	function handleNameChange(e: CustomEvent<{ languageId: number; value: string }>) {
		updateBlogStoreVariantValue(e.detail.languageId, 'name', e.detail.value);
	}

	function handleDescriptionChange(e: CustomEvent<{ languageId: number; value: string }>) {
		updateBlogStoreVariantValue(e.detail.languageId, 'description', e.detail.value);
	}

	function handleBlogValueChangeEvent(e: any, key: keyof Blog) {
		changeBlogValue(key, e.target.value);
	}

	function changeBlogValue(key: keyof Blog, value: any) {
		blogStore.update((b) => {
			return {
				...b,
				[key]: value
			};
		});
	}
</script>

<BlogSettingsSave
	keys={[
		'logo_url',
		'icon_url',
		'cover_url',
		'social_facebook',
		'social_twitter',
		'social_linkedin',
		'social_youtube',
		'social_tiktok',
		'social_instagram',
		'social_github'
	]}
	variantKeys={['name', 'description']}
/>

<div class="settings">
	<VariantInput
		label={i18n.t('console.common.name')}
		caption={i18n.t('console.settings.general.nameCaption')}
		type="blog"
		obj={$blogStore}
		key="name"
		maxlength={160}
		on:change={handleNameChange}
	/>

	<VariantInput
		label={i18n.t('console.tools.import.description')}
		caption={i18n.t('console.settings.general.descriptionCaption')}
		type="blog"
		obj={$blogStore}
		key="description"
		maxlength={255}
		on:change={handleDescriptionChange}
	/>

	<SplitControl
		label={i18n.t('console.settings.general.logo')}
		caption={i18n.t('console.settings.general.logoCaption')}
	>
		<ImageSetting
			src={$blogStore.logo_url}
			on:change={(e) => changeBlogValue('logo_url', e.detail)}
		/>
	</SplitControl>

	<SplitControl
		label={i18n.t('console.settings.general.icon')}
		caption={i18n.t('console.settings.general.iconCaption')}
	>
		<ImageSetting
			src={$blogStore.icon_url}
			on:change={(e) => changeBlogValue('icon_url', e.detail)}
		/>
	</SplitControl>

	<SplitControl
		label={i18n.t('console.settings.general.cover')}
		caption={i18n.t('console.settings.general.coverCaption')}
	>
		<ImageSetting
			src={$blogStore.cover_url}
			on:change={(e) => changeBlogValue('cover_url', e.detail)}
		/>
	</SplitControl>

	<!-- <SplitControl
		label="Hyvor Blogs Branding"
		caption={'Show "Published with Hyvor Blogs" message'}
	>
		<div class="branding-wrap" class:disabled={!hasGrowthPlan}>
			<Switch
				checked={brandingValue}
				disabled={!hasGrowthPlan}
				on:change={handleBrandingChange}
			/>
		</div>

		{#if !hasGrowthPlan}
			<div style="display: flex; margin-top: 5px; margin-left:1px">
				<Text small light
					><strong>Growth or a higher plan</strong> is required to disable branding.&nbsp</Text
				>
				<Link href={consoleUrlWithBlog('/billing')}>
					<small>Upgrade Now!</small>
				</Link>
			</div>
		{/if}
	</SplitControl> -->

	<SplitControl
		label={i18n.t('console.settings.general.socialMedia')}
		caption={i18n.t('console.settings.general.socialMediaCaption')}
	>
		{#snippet nested()}
			<div>
				<SplitControl label="Facebook">
					<TextInput
						block
						value={$blogStore.social_facebook}
						on:input={(e) => handleBlogValueChangeEvent(e, 'social_facebook')}
					/>
				</SplitControl>

				<SplitControl label="X (Twitter)">
					<TextInput
						block
						value={$blogStore.social_twitter}
						on:input={(e) => handleBlogValueChangeEvent(e, 'social_twitter')}
					/>
				</SplitControl>

				<!-- Linkedin -->
				<SplitControl label="Linkedin">
					<TextInput
						block
						value={$blogStore.social_linkedin}
						on:input={(e) => handleBlogValueChangeEvent(e, 'social_linkedin')}
					/>
				</SplitControl>

				<!-- Youtube -->
				<SplitControl label="Youtube">
					<TextInput
						block
						value={$blogStore.social_youtube}
						on:input={(e) => handleBlogValueChangeEvent(e, 'social_youtube')}
					/>
				</SplitControl>

				<!-- TikTok -->
				<SplitControl label="TikTok">
					<TextInput
						block
						value={$blogStore.social_tiktok}
						on:input={(e) => handleBlogValueChangeEvent(e, 'social_tiktok')}
					/>
				</SplitControl>

				<!-- Instagram -->
				<SplitControl label="Instagram">
					<TextInput
						block
						value={$blogStore.social_instagram}
						on:input={(e) => handleBlogValueChangeEvent(e, 'social_instagram')}
					/>
				</SplitControl>

				<!-- Github -->
				<SplitControl label="Github">
					<TextInput
						block
						value={$blogStore.social_github}
						on:input={(e) => handleBlogValueChangeEvent(e, 'social_github')}
					/>
				</SplitControl>
			</div>
		{/snippet}
	</SplitControl>
</div>

<style>
	.settings {
		flex: 1;
		overflow: auto;
		padding: 25px 30px;
	}

	.settings :global(.CodeMirror) {
		min-height: 200px;
	}

	/* .branding-wrap.disabled {
		opacity: 0.7;
		pointer-events: none;
	} */
</style>
