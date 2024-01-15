<script lang="ts">
	import { Text } from '@hyvor/design/components';
	import TruspilotIcon from './TruspilotIcon.svelte';
	import { IconBoxArrowUpRight } from '@hyvor/icons';
	import G2Icon from './G2Icon.svelte';
	import { ActionListItem } from '@hyvor/design/components';
	import { ActionList } from '@hyvor/design/components';
	import { Modal } from '@hyvor/design/components';
	import { Button } from '@hyvor/design/components';
    import { IconInfoCircleFill, IconDiscord, IconStarFill } from '@hyvor/icons';

    let ratingShown = false;
</script>
<div id="nav-bottom">

    <Button
        block
        variant="invisible"
        color="accent"
        align="start"
        on:click={() => ratingShown = true}
    >
        <IconStarFill slot="start" />
        Rate us
    </Button>



    <Button
        as="a"
        href="https://discord.gg/2WRJxQB"
        target="_blank"
        block
        variant="invisible"
        color="accent"
        align="start"
    >
        <IconDiscord slot="start" />
        Join our Discord
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
            <a 
                href="https://www.g2.com/products/hyvor-blogs/reviews"
                target="_blank"
            >
                <ActionListItem>
                    <G2Icon slot="start" />
                    <span>Rate us on G2</span>
                    <Text small light>
                        How is our product?
                    </Text>
                    <IconBoxArrowUpRight slot="end" size={14} />
                </ActionListItem>
            </a>

            <a 
                href="https://www.trustpilot.com/review/hyvor.com"
                target="_blank"
            >
                <ActionListItem>
                    <TruspilotIcon slot="start" />
                    <span>Rate us on Trustpilot</span>
                    <Text small light>
                        How is our customer service?
                    </Text>
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
        :global(button), :global(a) {
            color: var(--text)!important;
            font-weight: normal;
        }
    }
</style>