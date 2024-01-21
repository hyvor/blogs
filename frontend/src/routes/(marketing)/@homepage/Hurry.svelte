<script lang="ts">

	import { Button } from "@hyvor/design/components";
	import { IconBoxArrowUpRight } from "@hyvor/icons";
	import { slide } from "svelte/transition";

    let mainEl : HTMLDivElement;
    let fixedEl : HTMLDivElement;

    let showFixed = false;

    function handleScroll() {

        const { bottom } = mainEl.getBoundingClientRect();

        if (bottom - 55 <= 0) {
            showFixed = true;
        } else {
            showFixed = false;
        }

    }

</script>

<svelte:window on:scroll={handleScroll} />

<div 
    class="hds-box hurry"
    bind:this={mainEl}
>
    <div class="title">
        In a hurry?
    </div>

    Try Hyvor Blogs with a free <strong>temporary</strong> blog.
    No sign up required, not even an email address. Click & go.

    <div class="temp-button">
        <Button as="a" href="/console?temp" target="_blank">
            Create a temporary blog
            <IconBoxArrowUpRight slot="end" size={10} />
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

            <div>
                In a hurry? Try Hyvor Blogs with a <strong>free temporary</strong> blog.
            </div>

            <Button variant="outline" size="small" as="a" href="/console?temp" target="_blank">
                Create a temporary blog
                <IconBoxArrowUpRight slot="end" size={10} />
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

    .fixed {
        position: fixed;
        top: var(--header-height);
        left: 0;
        width: 100%;
        background: var(--accent-lightest);
        border-bottom: 1px solid var(--border);
        align-items: center;
        height: 50px;
        z-index: 10;
        display: flex;
        .inner {
            display: flex;
            gap: 10px;
            align-items: center;
        }
    }

</style>