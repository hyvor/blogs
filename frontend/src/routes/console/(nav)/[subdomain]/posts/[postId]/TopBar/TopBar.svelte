<script lang="ts">
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import { postStore, postVariantStore } from '../../postStore';
	import PostStatusTag from '../../PostStatusTag.svelte';
	import PreviewButton from '../Sidebar/Top/PreviewButton.svelte';
	import UnpublishButton from '../Sidebar/Top/UnpublishButton.svelte';
	import PublishButton from '../Sidebar/Top/PublishButton.svelte';
	import UpdateButton from '../Sidebar/Top/PublishedUpdate/UpdateButton.svelte';
	import PostLanguage from './PostLanguage.svelte';
	import SaveStatus from './SaveStatus.svelte';
	import CaretLeft from './CaretLeft.svelte';

	function getBackUrl() {
		const postData = $postStore;
		return consoleUrlWithBlog(postData && postData.is_page ? '/pages' : '/posts');
	}
</script>

<div class="post-top-bar hds-box">
	<a class="back-button" href={getBackUrl()}>
		<CaretLeft />
		Back
	</a>

	<div class="left">
		<PostStatusTag status={$postVariantStore.status} />
		<SaveStatus />
	</div>

	<div class="right">
		<PostLanguage />
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
		height: 46px;
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

	.left {
		flex: 1;
	}

	.left,
	.right {
		display: flex;
		align-items: center;
		gap: 10px;
	}
</style>
