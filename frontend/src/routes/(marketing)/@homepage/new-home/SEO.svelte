<script lang="ts">
    import Browser from "./Browser.svelte";
    import seoImg from './img/SEO.png';
    import seoFull from './img/seo-full.png';
    import IconBullseye from '@hyvor/icons/IconBullseye';
    import IconLink from '@hyvor/icons/IconLink';
    import IconClipBoard from '@hyvor/icons/IconClipboard';
    import IconTags from '@hyvor/icons/IconTags';


    interface Props {
        title: string;
        subtitle: string;
    }

    let { title, subtitle = undefined }: Props = $props();
</script>

<div class="above-fold" id="seo-section">

    <div class="left">

        <h1>
            Outperform your Competitors
<!--            with Built-in SEO-->
        </h1>

        <h2>Spend your time creating content while your competitors are stuck with plugins.</h2>

        <div class="bento-grid">
            <div class="int">
               <IconBullseye class="int-icon" size="30px" />
                <div class="seo-feature-name">SEO Analyzer</div>
            </div>

            <div class="int">
               <IconLink class="int-icon" size="30px" />
                <div class="seo-feature-name">Post Link Analyzer</div>
            </div>

            <div class="int">
               <IconClipBoard class="int-icon" size="30px" />
                <div class="seo-feature-name">Full Blog Link Analyzer</div>
            </div>

            <div class="int">
               <IconTags class="int-icon" size="30px" />
                <div class="seo-feature-name">Meta tags</div>
            </div>

            <div class="int">
               <IconBullseye class="int-icon" size="30px" />
                <div class="seo-feature-name">Robots.txt</div>
            </div>
        </div>


    </div>

    <div class="right">
        <div class="main-browser">
            <Browser image={seoFull} />
        </div>

        <div class="another-browser">
             <Browser image={seoImg} />
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
        padding-left: 75px;
        padding-bottom: 75px;

        .left {
            flex: 1;
        }

        .left h1 {
            font-size: 60px !important;
            margin: 0;
        }

        .left .sassy {
            margin-top: 20px;
            font-size: 40px !important;
            color: var(--grey-dark);
            font-weight: 500;
            text-align: end;
        }

        .right {
            flex: 1;
            padding-left: 25px;
            position: relative;




            .main-browser {
                width: 150%; /* did this to make it extend over the screen edge */
                max-width: none; /* did this to remove max-width thing */
                margin-left: 0;
                //background-color: #191e19;
                padding-top: 70px;
                padding-bottom: 70px;
                padding-left: 70px;
                border-radius: 20px;
                filter: blur(1px);
                opacity: 0.9;

            }

            .another-browser {
                position: absolute;
                width: 400px;
                bottom: 30px;
                right: 0;
                border-radius: 20px;
                box-shadow: var(--box-shadow);
            }
        }

        //
        @keyframes heroEnter {
            0% {
                //transform: translateX(30px) translateY(0) translateZ(20px);
                transform: translateX(300PX) translateY(0) translateZ(20px);


            }
            100% {
                //transform: translateX(0) translateY(0) translateZ(0);
                transform: translateX(0) translateY(0) translateZ(0);
                opacity: 1;
                filter: blur(0);
            }
        }

        @keyframes heroEnterDelayed {
            0% {
                transform: translateX(200px) translateY(30px) translateZ(10px);
                //transform: translateX(0) translateY(30px) translateZ(10px);
                opacity: 0.1;

            }
            100% {
                //transform: translateX(0) translateY(0) translateZ(0);
                opacity: 1;
                transform: translateX(0) translateY(0) translateZ(0);
                //filter: blur(0);
            }
        }

        @keyframes subtleFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .main-browser {
            animation: heroEnter 1s ease-out, subtleFloat 8s ease-in-out infinite 2s;
        }

        .another-browser {
            animation: heroEnterDelayed 3s ease-out, subtleFloat 6s ease-in-out infinite 3s reverse;
            //    differed the appearance and float animation a bit using different timings
        }

        .bento-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 40px;
            margin-top: 40px;
        }

        .int {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 25px;
            margin-bottom: 10px;
            color: var(--accent);
        }

        .seo-feature-name{
            color:  var(--gray-dark);
        }

        .int span {
            opacity: 0.2;
        }

        .int-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        h1 {
            margin: 0;
            font-size:40px;
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
        margin-top: 30px;
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
</style>
