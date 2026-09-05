<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import { postVariantStore } from '../../../postStore';
	import { replyingPostVariantsStore } from '../../../../agent/agentChatSessionStore';

	let replying = $derived($replyingPostVariantsStore.has($postVariantStore.id));
</script>

{#if replying}
	<Loader size={10} colorTrack="transparent" />
{/if}
