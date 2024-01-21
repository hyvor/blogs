<script>
	import { IconBoxArrowUpRight } from '@hyvor/icons';
	import { Button } from '@hyvor/design/components';
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

        <div class="hds-box hurry">
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

    }

</style>