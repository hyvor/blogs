
<script lang="ts">
    import Browser from "../Browser.svelte";
    import seoImg from '../img/SEO.png';
    import seoFull from '../img/seo-full.png'
    import logoCfWorkers from "../img/cf-workers.svg";
    import logoDocker from "../img/docker.svg";
    import logoNext from "../img/next.svg";
    import logoLaravel from "../img/laravel.svg";
    import logoSymfony from "../img/symfony.svg";
    import {Tag} from "@hyvor/design/components";
    import Type from "./Type.svelte";


    interface Props {
        title: string;
        subtitle: string;
    }

    let { title, subtitle = undefined }: Props = $props();
</script>

<div class="above-fold" id="seo-section">

    <div class="left">

        <h1>
            Host Anywhere
        </h1>

        <h2>
            Subdomain, custom domain, sub-directory... anywhere!

        </h2>

        <div class="types hds-container">
            <Type
                name="Default Subdomain"
                domain="subdomain.hyvorblogs.io"
                features={[
			'Best option to <strong>get started</strong>',
			'Available for all blogs by default'
		]}
            />

            <Type
                name="Custom Domain"
                domain="blog.example.com"
                features={[
			'Best option for a <strong>professional blog</strong>',
			'<a href="https://blogs.hyvor.com/docs/custom-domain" target="_blank">Easy to setup</a> by changing DNS records.',
			'Root (<b>example.com</b>) or subdomain (<strong>blog.example.com</strong>)',
			'Free SSL certificate'
		]}
            />

            <Type
                name="Sub-directory"
                domain="example.com/blog"
                features={[
			'Best option for <strong>SEO</strong>',
			'Serve your blog from your web app',
			'Reverse proxy support'
		]}
            >
                <div
                    style="
                margin-top:15px;
                border-top:1px solid var(--accent-light);
                margin-left:-25px;
                margin-right:-25px;
            "
                ></div>

                <div class="sub-dir-note">
                    Use one of the following methods to host your blog in a sub-directory:
                </div>

                <div class="methods">
                    <a href="https://hyvor.com/blog/cloudflare-workers-blog" target="_blank">
                        <img src={logoCfWorkers} alt="Cloudflare Workers Logo" />
                        <span>
					Cloudflare <br /> Workers <br />
					<Tag color="blue" size="small">No-code</Tag>
				</span>
                    </a>

                    <a href="https://hyvor.com/blog/docker-subdirectory-blog" target="_blank">
                        <img src={logoDocker} alt="Docker Logo" />
                        <span> Docker </span>
                    </a>

                    <a href="https://hyvor.com/blog/nextjs-blog" target="_blank">
                        <img src={logoNext} alt="Next.js Logo" />
                        <span> Next.js </span>
                    </a>

                    <a href="https://hyvor.com/blog/laravel-blog" target="_blank">
                        <img src={logoLaravel} alt="Laravel Logo" />
                        <span> Laravel </span>
                    </a>

                    <a href="https://hyvor.com/blog/symfony-blog" target="_blank">
                        <img src={logoSymfony} alt="Symfony Logo" />
                        <span> Symfony </span>
                    </a>
                </div>
            </Type>
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
        padding-left: 55px;
        padding-bottom: 75px;

        .left {
            flex: 1;
        }

        .left h1 {
            font-size: 60px !important;
            margin: 0 !important;
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

        .types {
            display: flex;
            gap: 20px;
            flex-direction: column;
            width: 700px;
            max-width: 100%;
            margin: auto;
            margin-top: 40px;
            padding: 0 !important;
        }
        .methods {
            flex: 1;
            display: flex;
            justify-content: center;
            gap: 25px;
            padding: 15px;
            a {
                display: inline-flex;
                flex-direction: column;
                align-items: center;
            }
            img {
                width: 30px;
                height: 30px;
                margin-bottom: 10px;
            }
            span {
                text-align: center;
                font-size: 14px;
            }
        }
        .sub-dir-note {
            margin-top: 15px;
            margin-bottom: 15px;
            color: var(--text-light);
            font-size: 14px;
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

        .int span {
            opacity: 0.2;
        }

        .int-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
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

    .testmonial {
        display: flex;
        justify-content: flex-start;
        margin-top: 20px;
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
