<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import Hurry from "./Hurry.svelte";
    import hbIcon from '$lib/img/logo-social.png';
    import Browser from "./new-home/Browser.svelte";
    import  TestSS from './new-home/img/test-ss.png';
    import BlogPage from './new-home/img/blog-page.png';
    import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';


    interface Props {
        title: string;
        subtitle: string;
        integrationIcon?: string | undefined;
    }

    let { title, subtitle, integrationIcon = undefined }: Props = $props();
</script>

<div class="above-fold">

    <div class="left">

        {#if integrationIcon}
            <div class="int">
                <img src={integrationIcon} alt="Integration icon" class="int-icon" />
                <span>+</span>
                <img src={hbIcon} alt="Hyvor Blogs" class="int-icon" />
            </div>
        {/if}

        <h1>
            <span class="all-in-one">
                All-in-one
            </span><br />
            Blogging Platform
        </h1>

        <h2>
            {subtitle}
        </h2>

        {#if integrationIcon}

        {/if}

<!--        <div class="checks">-->
<!--            <div class="check">-->
<!--                <IconCheckCircleFill />-->
<!--                Multilingual-->
<!--            </div>-->

<!--            <div class="check">-->
<!--                <IconCheckCircleFill />-->
<!--                In-built SEO-->
<!--            </div>-->

<!--            <div class="check">-->
<!--                <IconCheckCircleFill />-->
<!--                Customizable themes-->
<!--            </div>-->

<!--            <div class="check">-->
<!--                <IconCheckCircleFill />-->
<!--                Custom domain & subdirectory hosting-->
<!--            </div>-->
<!--        </div>-->

        <div class="buttons">

            <Button as="a" href="/console?signup" size="large">
                Start your blog
            </Button>

            <Button as="a" href="/customers" size="large" variant="outline">
                See customer blogs
            </Button>

        </div>

        <div class="hurry">
            <Hurry />
        </div>

    </div>

    <div class="right">
          <div class="main-browser">
              <Browser image={TestSS} />
          </div>
          <div class="another-browser">
              <Browser image={BlogPage} />
          </div>
    </div>

</div>


<style lang="scss">
    :global(body) {
        overflow-x: hidden;
    }

    .checks {
        margin-top: 30px;
    }

    .check {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
        font-size: 18px;
    }

    .check :global(svg) {
        color: var(--accent);
    }

    .above-fold {

        display: flex;
        padding-top: 75px;
        padding-right: 50px;
        padding-left: 55px;
        padding-bottom: 75px;
        margin-bottom: 70px;

        .left {
            flex: 1;
        }

        .right {
            flex: 1;
            padding-left: 25px;
            position: relative;

            .main-browser {
                //width: 150%; /* did this to make it extend over the screen edge */
                //max-width: none; /* did this to remove max-width thing */
                margin-left: 0;

                width: clamp(100%, 150%, 900px); // Intentional overflow effect
                margin-left: 0;

                animation: heroEnter 1s ease-out, subtleFloat 8s ease-in-out infinite 2s;
            }

            .another-browser {
                position: absolute;
                top: 270px;
                right: 150px;
                width: 90%;


                animation: heroEnterDelayed 1.2s ease-out, subtleFloat 6s ease-in-out infinite 3s reverse;
            //    differed the appearance and float animation a bit using different timings
            }
        }


        @keyframes heroEnter {
            0% {
                transform: translateX(30px) translateY(0);
                //transform: translateX(60px) translateY(-20px);
                opacity: 0.9;
                //filter: blur(2gpx);
            }
            100% {
                transform: translateX(0) translateY(0);
                opacity: 1;
                filter: blur(0);
            }
        }

        @keyframes heroEnterDelayed {
            0% {
                //transform: translateX(-40px) translateY(30px);
                transform: translateX(0) translateY(30px);
                opacity: 0.9;
                //filter: blur(1gpx);
            }
            100% {
                transform: translateX(0) translateY(0);
                opacity: 1;
                //filter: blur(0);
            }
        }

        @keyframes subtleFloat {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            33% {
                transform: translateY(-3px) rotate(0.5deg);
            }
            66% {
                transform: translateY(-1px) rotate(-0.3deg);
            }
        }


        .int {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 35px;
            margin-bottom: 10px;
            span {
                opacity: 0.2;
            }
        }

        .int-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        h1 {
            margin: 0 !important;
            font-size:60px;
            line-height: 1.1;
        }

        h1 .all-in-one {
            font-size: 45px;
        }

        h2 {
            font-weight: normal;
            font-size: 25px;
            color: var(--grey-dark);
            margin: 0;
            padding-top: 20px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
    }

    .trial-checks {
        margin-top:25px;
        margin-left:5px;
    }

    .hurry {
        margin-top: 50px;
        width: 400px;
    }

    @media (max-width: 992px) {
        .above-fold {
            flex-direction: column;
            text-align: center;
            padding-left: 20px;
            padding-right: 20px;
            .right {
                display: none;
            }
            .buttons {
                justify-content: center;
            }
        }

        .int {
            justify-content: center;
        }

        .trial-checks {
            margin-left: 0;
            display: flex;
            justify-content: center;
        }

        .hurry {
            display: flex;
            justify-content:  center;
            margin-left: 0;
            max-width: 100%;
            width: 500px;
        }
    }

//    media queries for 768px, 1024px, 1440px, 2560px set a max width
  @media (min-width: 1440px) {
        .above-fold {
            max-width: 1700px;
            margin: auto;
        }
  }

</style>
