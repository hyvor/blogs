<script lang="ts">
	import { Button, Divider } from '@hyvor/design/components';
	import Hurry from "./Hurry.svelte";
    import hbIcon from '$lib/img/logo-social.png';
    import Browser from "./new-home/Browser.svelte";
    import  TestSS from './new-home/img/test-ss.png';
    import MistLight from './new-home/img/mist-light.png';

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
              <Browser image={TestSS} link="blogs.hyvor.com/console" />
          </div>
          <div class="another-browser">
              <Browser image={MistLight} link="pensee.com" />
          </div>
    </div>

</div>


<style lang="scss">
    :global(body) {
        overflow-x: hidden;
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
            font-size: 28px;
            color: var(--grey-dark);
            margin: 0;
            padding-top: 30px;
        //    40
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }


    }

    .hurry {
        margin-top: 50px;
        width: 400px;
    }
//    @media (max-width: 992px) {
//        .above-fold {
//            flex-direction: column;
//            text-align: center;
//            padding-left: 20px;
//            padding-right: 20px;
//            .right {
//                display: none;
//            }
//            .buttons {
//                justify-content: center;
//            }
//        }
//
//        .int {
//            justify-content: center;
//        }
//
//        .hurry {
//            display: flex;
//            justify-content:  center;
//            margin-left: 0;
//            max-width: 100%;
//            width: 500px;
//        }
//    }
//
////    media queries for 768px, 1024px, 1440px, 2560px set a max width
//  @media (min-width: 1440px) {
//        .above-fold {
//            max-width: 1700px;
//            margin: auto;
//        }
//
//    .hurry {
//            width: 500px;
//        }
//  }
    /* Tablet styles */
    @media (max-width: 1024px) and (min-width: 769px) {
        .above-fold {
            padding-left: 30px;
            padding-right: 30px;
            padding-top: 60px;
            padding-bottom: 60px;
            margin-bottom: 50px;

            h1 {
                font-size: 52px;
            }

            h1 .all-in-one {
                font-size: 38px;
            }

            h2 {
                font-size: 24px;
                padding-top: 25px;
            }

            .right {
                display: none;
                //padding-left: 15px;
                //
                //.main-browser {
                //    width: clamp(100%, 130%, 800px);
                //}
                //
                //.another-browser {
                //    top: 240px;
                //    right: 120px;
                //    width: 85%;
                //}
            }
        }

        .hurry {
            width: 350px;
        }
    }

    /* Mobile styles */
    @media (max-width: 768px) {
        .above-fold {
            flex-direction: column;
            text-align: center;
            padding-left: 20px;
            padding-right: 20px;
            padding-top: 40px;
            padding-bottom: 40px;
            margin-bottom: 40px;

            .left {
                margin-bottom: 30px;
            }

            .right {
                //display: block;
                //padding-left: 0;
                //position: relative;
                //
                //.main-browser {
                //    width: 100%;
                //    max-width: 100%;
                //    margin-left: 0;
                //    animation: mobileHeroEnter 0.8s ease-out;
                //}
                //
                //.another-browser {
                //    display: none; // Hide secondary browser on mobile for cleaner look
                //}
                display: none;
            }

            .int {
                justify-content: center;
                font-size: 28px;
                gap: 12px;
                margin-bottom: 15px;

                .int-icon {
                    width: 40px;
                    height: 40px;
                }
            }

            h1 {
                font-size: 42px;
                margin-bottom: 0 !important;
            }

            h1 .all-in-one {
                font-size: 32px;
            }

            h2 {
                font-size: 20px;
                padding-top: 20px;
                padding-left: 10px;
                padding-right: 10px;
            }

            .buttons {
                justify-content: center;
                flex-direction: column;
                gap: 12px;
                margin-top: 25px;
                align-items: center;
            }

            // Make buttons full width on mobile
            .buttons :global(.button) {
                width: 100%;
                max-width: 280px;
            }
        }

        .hurry {
            display: flex;
            justify-content: center;
            margin-left: 0;
            margin-top: 35px;
            max-width: 100%;
            width: 100%;
            padding: 0 10px;
        }

        @keyframes mobileHeroEnter {
            0% {
                transform: translateY(20px);
                opacity: 0.8;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
    }

    /* Small mobile devices */
    @media (max-width: 480px) {
        .above-fold {
            padding-left: 15px;
            padding-right: 15px;
            padding-top: 30px;

            h1 {
                font-size: 36px;
            }

            h1 .all-in-one {
                font-size: 28px;
            }

            h2 {
                font-size: 18px;
                padding-top: 15px;
            }

            .int {
                font-size: 24px;
                gap: 10px;

                .int-icon {
                    width: 35px;
                    height: 35px;
                }
            }

            .buttons {
                margin-top: 20px;
                gap: 10px;
            }

            .buttons :global(.button) {
                max-width: 250px;
                font-size: 16px;
            }
        }

        .hurry {
            margin-top: 25px;
            padding: 0 5px;
        }
    }

    /* Large screens - keep your existing max-width constraint */
    @media (min-width: 1440px) {
        .above-fold {
            max-width: 1700px;
            margin: auto;
        }

        .hurry {
            width: 500px;
        }
    }

</style>
