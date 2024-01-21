<script>
	import { IconBoxArrowUpRight } from '@hyvor/icons';
	import { Button } from '@hyvor/design/components';
	import Hurry from './Hurry.svelte';
</script>

<div class="hds-container above-fold">

    <div class="left">

        <h1>
            All-in-one Blogging Platform
        </h1>

        <h2>
            Hyvor Blogs is a platform that helps you to create a blog, manage it, and grow it without having to worry about managing servers, databases, and other technical stuff.
        </h2>

        <div class="buttons">

            <Button as="a" href="/console?signup" size="large">
                Start your blog
            </Button>

            <Button as="a" href="/customers" size="large" variant="outline">
                See customer blogs
            </Button>

        </div>

    </div>

    <div class="right">
        <Hurry />
    </div>

</div>


<style lang="scss">

    .above-fold {

        display: flex;
        padding-top: 75px;

        .left {
            flex: 1;
        }

        .right {
            flex: 1;
            padding-left: 25px;
        }

        h1 {
            margin: 0;
            font-size:40px;
        }

        h2 {
            font-weight: normal;
            font-size: 20px;
            color: var(--text-light);
            margin: 0;
            padding-top: 20px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
    }

</style>