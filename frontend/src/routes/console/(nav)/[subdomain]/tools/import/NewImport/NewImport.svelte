<script lang="ts">
	import {
		Button,
		Link,
		Loader,
		Modal,
		Radio,
		SplitControl,
		Switch,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { testSitemapUrl, type TestSitemapResponse, importSitemap } from '../importActions';
	import dayjs from 'dayjs';
	import { createEventDispatcher } from 'svelte';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	let importFrom = $state('sitemap');

	let sitemapUrl = $state('');

	let titleCssSelector = $state('');
	let descriptionCssSelector = $state('');
	let contentCssSelector = $state('');
	let contentExcludeCssSelector = $state('');
	let publishedDateCssSelector = $state('');

	let importImages = $state(true);
	let slugExclude = $state('');
	let testUrl = $state('');

	let testData: TestSitemapResponse | null = $state(null);

	let isTestLoading = $state(false);

	function startTesting() {
		if (testUrl.trim() === '') {
			return toast.error(i18n.t('console.tools.import.enterTestUrl'));
		}

		isTestLoading = true;

		testSitemapUrl({
			url: testUrl,
			slug_exclude: slugExclude,
			css: {
				title: titleCssSelector,
				description: descriptionCssSelector,
				content: contentCssSelector,
				content_exclude: contentExcludeCssSelector,
				published_date: publishedDateCssSelector
			}
		})
			.then((res) => (testData = res))
			.catch((err) => toast.error(err.message))
			.finally(() => (isTestLoading = false));
	}

	const dispatch = createEventDispatcher();
	function dispatchComplete() {
		dispatch('complete');
	}

	function handleSitemapImport() {
		const toastId = toast.loading(i18n.t('console.tools.import.importingSitemap'));

		importSitemap({
			sitemap_url: sitemapUrl,
			slug_exclude: slugExclude,
			css: {
				title: titleCssSelector,
				description: descriptionCssSelector,
				content: contentCssSelector,
				content_exclude: contentExcludeCssSelector,
				published_date: publishedDateCssSelector
			},
			import_images: importImages
		})
			.then(() => {
				toast.success(i18n.t('console.tools.import.sitemapImportStarted'), { id: toastId });
				dispatchComplete();
			})
			.catch((err) => {
				toast.error(err.message, { id: toastId });
			});
	}
</script>

<SplitControl
	label={i18n.t('console.tools.import.importFrom')}
	caption={i18n.t('console.tools.import.importFromCaption')}
>
	<Radio name="import-from" value="sitemap" bind:group={importFrom}
		>{i18n.t('console.tools.import.sitemapOption')}</Radio
	>
</SplitControl>

<SplitControl
	label={i18n.t('console.tools.import.sitemapUrl')}
	caption={i18n.t('console.tools.import.sitemapUrlCaption')}
>
	<TextInput placeholder="https://..." block bind:value={sitemapUrl} />
</SplitControl>

<SplitControl
	label={i18n.t('console.tools.import.cssSelectors')}
	caption={i18n.t('console.tools.import.cssSelectorsCaption')}
>
	{#snippet nested()}
		<div>
			<SplitControl
				label={i18n.t('console.tools.import.postContent')}
				caption={i18n.t('console.tools.import.required')}
			>
				<TextInput block bind:value={contentCssSelector} />
			</SplitControl>

			<SplitControl label={i18n.t('console.tools.import.postTitle')}>
				<TextInput block bind:value={titleCssSelector} />
			</SplitControl>

			<SplitControl label={i18n.t('console.tools.import.postDescription')}>
				<TextInput block bind:value={descriptionCssSelector} />
			</SplitControl>

			<SplitControl
				label={i18n.t('console.tools.import.postContentExclude')}
				caption={i18n.t('console.tools.import.postContentExcludeCaption')}
			>
				<TextInput block bind:value={contentExcludeCssSelector} />
			</SplitControl>

			<SplitControl label={i18n.t('console.tools.import.postPublishedDate')}>
				<TextInput block bind:value={publishedDateCssSelector} />
			</SplitControl>
		</div>
	{/snippet}
</SplitControl>

<SplitControl
	label={i18n.t('console.tools.import.importImages')}
	caption={i18n.t('console.tools.import.importImagesCaption')}
>
	<Switch bind:checked={importImages} />
</SplitControl>

<SplitControl
	label={i18n.t('console.tools.import.slugExclude')}
	caption={i18n.t('console.tools.import.slugExcludeCaption')}
>
	<TextInput block bind:value={slugExclude} />
</SplitControl>

<SplitControl
	label={i18n.t('console.tools.import.test')}
	caption={i18n.t('console.tools.import.testCaption')}
>
	<TextInput
		block
		bind:value={testUrl}
		placeholder={i18n.t('console.tools.import.testUrlPlaceholder')}
	/>

	<div style="margin-top:10px;">
		<Button size="small" on:click={startTesting}>
			Test {#snippet end()}
				<Loader
					state={isTestLoading ? 'loading' : 'none'}
					size="small"
					colorTrack="transparent"
					color="white"
				/>
			{/snippet}
		</Button>
	</div>
</SplitControl>

<div class="footer">
	<div class="note" style="margin-bottom: 20px;">
		{i18n.t('console.tools.import.testNote')}
	</div>

	<Button on:click={handleSitemapImport}>{i18n.t('console.tools.import.importSitemap')}</Button>
</div>

{#if testData}
	<Modal
		show={testData !== null}
		title={i18n.t('console.tools.import.testResults')}
		on:close={() => (testData = null)}
		footer={{
			cancel: {
				text: i18n.t('console.common.close')
			},
			confirm: false
		}}
		on:cancel={() => (testData = null)}
	>
		<SplitControl label={i18n.t('console.tools.import.url')} flex={[1, 4]}>
			<Link href={testData.url} target="_blank">
				{testData.url}
			</Link>
		</SplitControl>

		<!-- SLUG -->
		<SplitControl label={i18n.t('console.common.slug')} flex={[1, 4]}>
			{testData.data.slug}
		</SplitControl>

		<!-- Title -->
		<SplitControl label={i18n.t('console.tools.import.title')} flex={[1, 4]}>
			{testData.data.title}
		</SplitControl>

		<!-- Description -->
		<SplitControl label={i18n.t('console.tools.import.description')} flex={[1, 4]}>
			{testData.data.description}
		</SplitControl>

		<!-- Content -->
		<SplitControl label={i18n.t('console.tools.import.content')} flex={[1, 4]}>
			<div class="test-content">
				{@html testData.data.content_html}
			</div>
		</SplitControl>

		<!-- Published Date -->
		<SplitControl label={i18n.t('console.tools.import.publishedDate')} flex={[1, 4]}>
			{dayjs.unix(testData.data.published_at).format('MMMM D, YYYY')}
		</SplitControl>

		<!-- Featured Image -->
		<SplitControl label={i18n.t('console.tools.import.featuredImage')} flex={[1, 4]}>
			{#if testData.data.featured_image_url}
				<img src={testData.data.featured_image_url} alt={i18n.t('console.posts.status.featured')} />
			{/if}
		</SplitControl>
	</Modal>
{/if}

<style lang="scss">
	.footer {
		padding: 20px 100px;
		text-align: center;
	}

	.test-content {
		:global(a:not([href^='#'])) {
			color: var(--link);
		}
		:global(pre) {
			overflow: auto;
		}
		:global(blockquote) {
			border-left: 3px solid #000;
			padding-left: 20px;
		}
		:global(img) {
			max-width: 100%;
		}
		:global(aside) {
			display: flex;
			padding: 10px;
			gap: 10px;
			border-radius: 15px;
		}
	}

	img {
		max-width: 100%;
	}
</style>
