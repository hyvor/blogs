<script lang="ts">

	import { Button } from "@hyvor/design/components";
	import { IconBoxArrowUpRight } from "@hyvor/icons";
	import { slide } from "svelte/transition";

    let mainEl : HTMLDivElement = $state();
    let fixedEl : HTMLDivElement = $state();

    let showFixed = $state(false);

    function handleScroll() {

        const { bottom } = mainEl.getBoundingClientRect();

        if (bottom - 55 <= 0) {
            showFixed = true;
        } else {
            showFixed = false;
        }

    }

</script>

<svelte:window onscroll={handleScroll} />

<div 
    class="hds-box hurry"
    bind:this={mainEl}
>
    <div class="title">
        In a hurry?
    </div>

    Try Hyvor Blogs with a free temporary blog created in a few seconds.
    <strong>No sign up required</strong>.

    <div class="temp-button">
        <Button as="a" href="/console?temp" target="_blank">
            Create a temporary blog
            {#snippet end()}
                        <IconBoxArrowUpRight  size={10} />
                    {/snippet}
        </Button>
    </div>
    <div class="temp-note">
        Temporary blogs are deleted after 24 hours.
    </div>
</div>


{#if showFixed}
    <div
        class="fixed"
        bind:this={fixedEl}
        transition:slide={{ duration: 200 }}
    >

        <div class="hds-container inner">

            <div class="desktop">
                Try Hyvor Blogs with a free temporary blog. <strong>No sign up required</strong>.
            </div>

            <div class="mobile">
                In a hurry?
            </div>

            <Button
                color="input"
                variant="outline" 
                size="small" 
                as="a" 
                href="/console?temp"
                target="_blank"
            >
                <span class="desktop">Create a temporary blog</span>
                <span class="mobile">
                    Create temp blog
                </span>
                {#snippet end()}
                                <IconBoxArrowUpRight  size={10} />
                            {/snippet}
            </Button>

        </div>

    </div>
{/if}

<style lang="scss">

    .hurry {
        padding: 25px 30px;

        .title {
            font-weight: 600;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .temp-button {
            margin-top: 15px;
        }

        .temp-note {
            margin-top: 15px;
            font-size:14px;
            color: var(--text-light);
        }
    }

    .desktop {
        display: block;
    }
    .mobile {
        display: none;
    }

    .fixed {
        position: fixed;
        top: var(--header-height);
        left: 0;
        width: 100%;
        background: var(--accent);
        color: var(--accent-text);
        border-bottom: 1px solid var(--border);
        align-items: center;
        height: 50px;
        z-index: 10;
        display: flex;
        .inner {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }
    }

    @media (max-width: 992px) {

        .desktop {
            display: none;
        }
        .mobile {
            display: block;
        }

    }

</style>