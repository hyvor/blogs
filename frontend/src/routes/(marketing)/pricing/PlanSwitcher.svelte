<script>
	import { Button } from "@hyvor/design/components";
	import { IconCaretLeft, IconCaretRight, IconChevronLeft } from "@hyvor/icons";
	import { PLANS, plansMax, plansStart } from "./pricing";

    function handleLower() {
        if ($plansStart > 0) {
            plansStart.set($plansStart - 1);
        }
    }

    function handleHigher() {
        if ($plansStart < PLANS.length - $plansMax) {
            plansStart.set($plansStart + 1);
        }
    }

</script>


<div class="wrap">
    <div class="left">
        &#8203;
    </div>

    <div class="right">

        <div class="lower">
            {#if $plansStart > 0}
                <Button size="small" variant="outline" on:click={handleLower}>
                    {#snippet start()}
                                        <IconCaretLeft size={12}  />
                                    {/snippet}
                    Lower {$plansMax === 1 ? '' : 'plans'}
                </Button>
            {/if}
        </div>

        <div class="higher">
            {#if $plansStart < PLANS.length - $plansMax}
                <Button size="small" variant="outline" on:click={handleHigher}>
                    Higher {$plansMax === 1 ? '' : 'plans'}
                    {#snippet end()}
                                        <IconCaretRight size={12}  />
                                    {/snippet}
                </Button>
            {/if}
        </div>

    </div>

</div>

<style lang="scss">
    .wrap {
        display: flex;
        margin-bottom: 15px;
    }
    .left {
        flex: 1;
    }
    .right {
        flex: 3;
        display: flex;
        .lower {
            flex: 1;
        }
    }
    @media (max-width: 1000px) {
        .left {
            flex: 2;
        }
        .right {
            flex: 3;
        }
    }
</style>