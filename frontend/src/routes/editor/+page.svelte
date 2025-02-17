<script>
	import Prosemirror from '../console/(nav)/[subdomain]/posts/[postId]/Body/Editor/Prosemirror.svelte';
</script>

<div class="wrap hds-box">
	<Prosemirror value={null} />
</div>

<style>
	.wrap {
		width: 650px;
		margin: 20px auto;
		height: calc(100vh - 40px);
	}
</style>
