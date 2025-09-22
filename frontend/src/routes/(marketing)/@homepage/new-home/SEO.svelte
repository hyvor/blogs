<script lang="ts">
    import Browser from "./Browser.svelte";
    import seoImg from './img/SEO.png';
    import seoFull from './img/seo-full.png';
    import IconBullseye from '@hyvor/icons/IconBullseye';
    import IconLink from '@hyvor/icons/IconLink';
    import IconClipBoard from '@hyvor/icons/IconClipboard';
    import IconTags from '@hyvor/icons/IconTags';
    import IconFiletypeTxt from '@hyvor/icons/IconFiletypeTxt';
    import IconFileEarmarkText from "@hyvor/icons/IconFileEarmarkText";
    import Review from "./@components/Review.svelte";


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

        <h2>We hande the SEO and speed. You don't need to worry about anything.</h2>

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
                <IconFileEarmarkText class="int-icon" size="30px" />
                <div class="seo-feature-name">llms.txt</div>
            </div>

            <div class="int">
               <IconTags class="int-icon" size="30px" />
                <div class="seo-feature-name">Meta tags</div>
            </div>

            <div class="int">
               <IconFiletypeTxt class="int-icon" size="30px" />
                <div class="seo-feature-name">Robots.txt</div>
            </div>
        </div>


        <div class="testmonial">
            <Review
                userName="Michael B."
                title="Boosted My Blog's Traffic!"
                country="USA"
                date="2024-06-15"
                review="The built-in SEO tools are fantastic! My blog's traffic has significantly increased since I started using them. Highly recommend!"
                stars={5}
                verified={true}
                />
        </div>


    </div>

    <div class="right">
        <div class="main-browser">
            <Browser image={seoFull} link="blogs.hyvor.com/console" />
        </div>

        <div class="another-browser">
             <Browser image={seoImg} link="blogs.hyvor.com/console" />
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
        margin-bottom: 50px;

        .left {
            flex: 1;
        }

        .left h1 {
            font-size: 60px !important;
            margin: 0;
        }

        .right {
            flex: 1;
            padding-left: 25px;
            position: relative;
        }

        .right .main-browser {
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

        .right .another-browser {
            position: absolute;
            width: 400px;
            bottom: 30px;
            right: 0;
            border-radius: 20px;
            box-shadow: var(--box-shadow);
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
            //font-size: 25px;
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--gray-dark);
        }

        .seo-feature-name{
            color:  var(--gray-dark);
        }

        h1 {
            margin: 0;
            font-size:40px;
        }

        h2 {
            font-weight: normal;
            //font-size: 25px;
            font-size: 20px;
            color: var(--grey-dark);
            margin: 0;
            padding-top: 20px;
        }
    }

    .testmonial {
        display: flex;
        justify-content: flex-start;
        margin-top: 25px;
    }

    .testmonial :global(.review-card){
        width: auto;
    }

    @media (max-width: 992px) {
        .above-fold {
            flex-direction: column;
            text-align: center;
            padding-left: 20px;
            padding-right: 20px;
        }

        .right {
            display: none;
        }

        .int {
            justify-content: center;
        }

        .bento-grid {
            display: flex !important;
            flex-direction: column !important;
            margin-top: 30px;
            justify-items: center;
        }

        .left h1 {
            font-size: 28px !important;
            line-height: 1.2;
        }

        .left h2 {
            font-size: 18px !important;
            line-height: 1.4;
        }

        .testmonial {
            justify-content: center;
        }


    }
</style>
