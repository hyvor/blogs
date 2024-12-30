<script lang="ts">
	import { Text } from '@hyvor/design/components';
	import TruspilotIcon from './TruspilotIcon.svelte';
	import { IconBoxArrowUpRight, IconChat, IconChatFill } from '@hyvor/icons';
	import G2Icon from './G2Icon.svelte';
	import { ActionListItem } from '@hyvor/design/components';
	import { ActionList } from '@hyvor/design/components';
	import { Modal } from '@hyvor/design/components';
	import { Button } from '@hyvor/design/components';
	import { IconInfoCircleFill, IconDiscord, IconStarFill } from '@hyvor/icons';

	let ratingShown = false;
</script>

<div id="nav-bottom">
	<!-- <div class="announcement-wrap">
        <Button
            as="a"
            href="https://hyvor.com/blog/hyvor-blogs-ui-update"
            target="_blank"
            block
            variant="fill-light"
            color="blue"
            align="start"
        >
            📣 A new look <IconBoxArrowUpRight size={14} slot="end" />
        </Button>
    </div> -->

	<Button
		block
		variant="invisible"
		color="accent"
		align="start"
		on:click={() => (ratingShown = true)}
	>
		<IconStarFill slot="start" />
		Rate us
	</Button>

	<Button
		as="a"
		href="https://hyvor.community"
		target="_blank"
		block
		variant="invisible"
		color="accent"
		align="start"
	>
		<IconChatFill slot="start" />
		Join community
	</Button>

	<Button
		as="a"
		href="/docs"
		target="_blank"
		block
		variant="invisible"
		color="accent"
		align="start"
	>
		<IconInfoCircleFill slot="start" />
		Docs
	</Button>
</div>

{#if ratingShown}
	<Modal
		size="small"
		title="Rate us"
		bind:show={ratingShown}
		footer={{
			confirm: false,
			cancel: {
				text: 'Close'
			}
		}}
	>
		<div style="margin-bottom:20px;">
			Do you enjoy using Hyvor Blogs? Rate us on G2 or Trustpilot. Your feedback is valuable to us.
		</div>

		<ActionList>
			<a href="https://www.g2.com/products/hyvor-blogs/reviews" target="_blank">
				<ActionListItem>
					<G2Icon slot="start" />
					<span>Rate us on G2</span>
					<Text small light>How is our product?</Text>
					<IconBoxArrowUpRight slot="end" size={14} />
				</ActionListItem>
			</a>

			<a href="https://www.trustpilot.com/review/hyvor.com" target="_blank">
				<ActionListItem>
					<TruspilotIcon slot="start" />
					<span>Rate us on Trustpilot</span>
					<Text small light>How is our customer service?</Text>
					<IconBoxArrowUpRight slot="end" size={14} />
				</ActionListItem>
			</a>
		</ActionList>
	</Modal>
{/if}

<style lang="scss">
	#nav-bottom {
		flex: 1;
		display: flex;
		justify-content: flex-end;
		flex-direction: column;
	}

	#nav-bottom {
		padding: 5px 15px;
		:global(button),
		:global(a) {
			color: var(--text);
			font-weight: normal;
		}
	}

	.announcement-wrap {
		margin-bottom: 15px;
		:global(.button-content) {
			justify-content: center !important;
		}
	}

	@media (max-width: 992px) {
		#nav-bottom {
			display: none;
		}
	}
</style>
