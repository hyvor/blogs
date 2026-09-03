<script lang="ts">
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import { postStore, postVariantStore } from '../../postStore';
	import PreviewButton from '../Sidebar/Top/PreviewButton.svelte';
	import UnpublishButton from '../Sidebar/Top/UnpublishButton.svelte';
	import PublishButton from '../Sidebar/Top/PublishButton.svelte';
	import UpdateButton from '../Sidebar/Top/PublishedUpdate/UpdateButton.svelte';
	import PostSidebar from '../Sidebar/PostSidebar.svelte';
	import PostLanguage from './PostLanguage.svelte';
	import CaretLeft from './CaretLeft.svelte';
	import { goto } from '$app/navigation';
	import PostStatusTag from '../../PostStatusTag.svelte';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	function getBackUrl() {
		const postData = $postStore;
		return consoleUrlWithBlog(postData && postData.is_page ? '/pages' : '/posts');
	}

	function handleBack() {
		goto(getBackUrl());
	}
</script>

<div class="post-top-bar">
	<button class="back-button" onclick={handleBack}>
		<CaretLeft />
		{i18n.t('console.postEditor.back')}
	</button>

	<div class="left">
		<PostStatusTag status={$postVariantStore.status} showIcon={false} size="small" />
		<PostLanguage />
	</div>

	<div class="sections">
		<PostSidebar />
	</div>

	<div class="right">
		<PreviewButton />
		<UnpublishButton />
		<PublishButton />
		<UpdateButton />
	</div>
</div>

<style>
	.post-top-bar {
		display: flex;
		align-items: center;
		border-bottom: 1px solid var(--border);
		background-color: var(--box-background);
		position: sticky;
		top: 0;
		z-index: 100;
		height: 42px;
	}

	.back-button {
		display: flex;
		align-items: center;
		gap: 5px;
		font-size: 14px;
		color: var(--text-light);
		vertical-align: middle;
		padding: 0 25px;
		height: 100%;
		border-radius: 20px 0 0 20px;
		transition: color 0.2s ease;
		line-height: 1;
	}
	.back-button:hover {
		color: var(--text);
	}

	.left,
	.right,
	.sections {
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.left {
		flex: 1;
		gap: 5px;
	}

	.left {
		flex: 1;
	}

	.sections {
		flex: 3;
		height: 100%;
		justify-content: center;
	}

	.right {
		justify-content: flex-end;
	}

	.right {
		padding-right: 25px;
	}
</style>
