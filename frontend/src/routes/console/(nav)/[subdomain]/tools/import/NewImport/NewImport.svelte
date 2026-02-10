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
			return toast.error('Please enter a URL to test');
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
		const toastId = toast.loading('Importing sitemap...');

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
				toast.success('Sitemap imported started. It may take a while.', { id: toastId });
				dispatchComplete();
			})
			.catch((err) => {
				toast.error(err.message, { id: toastId });
			});
	}
</script>

<SplitControl label="Import From" caption="Select a method to import data from">
	<Radio name="import-from" value="sitemap" bind:group={importFrom}>Sitemap (Live Website)</Radio>
</SplitControl>

<SplitControl
	label="Sitemap URL"
	caption="XML or TXT sitemap that contains links to all your posts."
>
	<TextInput placeholder="https://..." block bind:value={sitemapUrl} />
</SplitControl>

<SplitControl label="CSS Selectors" caption="Add CSS selectors to find data in your HTML pages.">
	{#snippet nested()}
		<div>
			<SplitControl label="Post Content" caption="Required">
				<TextInput block bind:value={contentCssSelector} />
			</SplitControl>

			<SplitControl label="Post Title">
				<TextInput block bind:value={titleCssSelector} />
			</SplitControl>

			<SplitControl label="Post Description">
				<TextInput block bind:value={descriptionCssSelector} />
			</SplitControl>

			<SplitControl
				label="Post Content Exclude"
				caption="To exclude elements from post content"
			>
				<TextInput block bind:value={contentExcludeCssSelector} />
			</SplitControl>

			<SplitControl label="Post Published Date">
				<TextInput block bind:value={publishedDateCssSelector} />
			</SplitControl>
		</div>
	{/snippet}
</SplitControl>

<SplitControl label="Import Images" caption="Copy images into your media library (recommended)">
	<Switch bind:checked={importImages} />
</SplitControl>

<SplitControl label="Slug Exclude" caption="Exclude a part of the URL from the slug (ex: /blog/)">
	<TextInput block bind:value={slugExclude} />
</SplitControl>

<SplitControl
	label="Test"
	caption="Test a page to make sure CSS selectors are working (recommended)"
>
	<TextInput block bind:value={testUrl} placeholder="URL to test" />

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
		Please test a few pages before importing the sitemap. If you need help, feel free to contact
		us.
	</div>

	<Button on:click={handleSitemapImport}>Import Sitemap</Button>
</div>

{#if testData}
	<Modal
		show={testData !== null}
		title="Test Results"
		on:close={() => (testData = null)}
		footer={{
			cancel: {
				text: 'Close'
			},
			confirm: false
		}}
		on:cancel={() => (testData = null)}
	>
		<SplitControl label="URL" flex={[1, 4]}>
			<Link href={testData.url} target="_blank">
				{testData.url}
			</Link>
		</SplitControl>

		<!-- SLUG -->
		<SplitControl label="Slug" flex={[1, 4]}>
			{testData.data.slug}
		</SplitControl>

		<!-- Title -->
		<SplitControl label="Title" flex={[1, 4]}>
			{testData.data.title}
		</SplitControl>

		<!-- Description -->
		<SplitControl label="Description" flex={[1, 4]}>
			{testData.data.description}
		</SplitControl>

		<!-- Content -->
		<SplitControl label="Content" flex={[1, 4]}>
			<div class="test-content">
				{@html testData.data.content_html}
			</div>
		</SplitControl>

		<!-- Published Date -->
		<SplitControl label="Published Date" flex={[1, 4]}>
			{dayjs.unix(testData.data.published_at).format('MMMM D, YYYY')}
		</SplitControl>

		<!-- Featured Image -->
		<SplitControl label="Featured Image" flex={[1, 4]}>
			{#if testData.data.featured_image_url}
				<img src={testData.data.featured_image_url} alt="Featured" />
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
