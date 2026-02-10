<script lang="ts">
	import PreviewButton from './PreviewButton.svelte';
	import PublishButton from './PublishButton.svelte';
	import UnpublishButton from './UnpublishButton.svelte';
	import UpdateButton from './PublishedUpdate/UpdateButton.svelte';
</script>

<div class="top hds-box">
	<div class="left">
		<PreviewButton />
	</div>
	<div class="right">
		<UnpublishButton />
		<PublishButton />
		<UpdateButton />
	</div>
</div>

<style>
	.top {
		padding: 15px 25px;
		margin-bottom: 15px;
		display: flex;
		align-items: center;
	}
	.top .left {
		flex: 1;
	}

	.right {
		display: flex;
		gap: 6px;
		align-items: center;
	}
</style>
