<script lang="ts">
	import { IconButton } from '@hyvor/design/components';
	import IconCaretLeftFill from '@hyvor/icons/IconCaretLeftFill';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import { postStore, postVariantStore } from '../../postStore';
	import PostStatusTag from '../../PostStatusTag.svelte';
	import PreviewButton from '../Sidebar/Top/PreviewButton.svelte';
	import UnpublishButton from '../Sidebar/Top/UnpublishButton.svelte';
	import PublishButton from '../Sidebar/Top/PublishButton.svelte';
	import UpdateButton from '../Sidebar/Top/PublishedUpdate/UpdateButton.svelte';
	import PostLanguage from './PostLanguage.svelte';
	import SaveStatus from './SaveStatus.svelte';

	function getBackUrl() {
		const postData = $postStore;
		return consoleUrlWithBlog(postData && postData.is_page ? '/pages' : '/posts');
	}
</script>

<div class="post-top-bar hds-box">
	<div class="left">
		<a href={getBackUrl()}>
			<IconButton variant="invisible" color="gray">
				<IconCaretLeftFill />
			</IconButton>
		</a>
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
		justify-content: space-between;
		padding: 8px 25px;
	}

	.left,
	.right {
		display: flex;
		align-items: center;
		gap: 10px;
	}
</style>
