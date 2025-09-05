
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

    <div class="title">

        <h1>
            Host Anywhere
        </h1>

        <h2>
            Subdomain, custom domain, sub-directory... anywhere!

        </h2>



    </div>

    <div class="hosting">
        <div class="browser-window">
            <div class="browser-header">
                <div class="buttons">
                    <span class="red"></span>
                    <span class="yellow"></span>
                    <span class="green"></span>
                </div>
                <div class="address-bar">subdomain.hyvorblogs.io</div>
            </div>
            <div class="browser-content">
                <Type
                    name="Default Subdomain"
                    domain="subdomain.hyvorblogs.io"
                    features={[
			'Best option to <strong>get started</strong>',
			'Available for all blogs by default'
		]}
                />
            </div>
        </div>

        <div class="browser-window">
            <div class="browser-header">
                <div class="buttons">
                    <span class="red"></span>
                    <span class="yellow"></span>
                    <span class="green"></span>
                </div>
                <div class="address-bar">subdomain.hyvorblogs.io</div>
            </div>
            <div class="browser-content">
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
            </div>
        </div>

        <div class="browser-window">
            <div class="browser-header">
                <div class="buttons">
                    <span class="red"></span>
                    <span class="yellow"></span>
                    <span class="green"></span>
                </div>
                <div class="address-bar">subdomain.hyvorblogs.io</div>
            </div>
            <div class="browser-content">
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

    </div>





</div>


<style>
    .above-fold {
        padding-top: 75px;
        padding-right: 50px;
        padding-left: 55px;
        padding-bottom: 75px;
    }
    .title{
        text-align: start;
        margin-bottom: 40px;
    }

    .title h1 {
        font-size: 60px !important;
        margin: 0 !important;
    }

    .title h2 {
        font-size: 20px !important;
        margin: 0 !important;
        font-weight: normal;
    }

    .browser-window {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        background: #fff;
        width: 600px;
    }

    .browser-header {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        background: #e3e3e3;
        border-bottom: 1px solid #ddd;
    }

    .buttons {
        display: flex;
        gap: 6px;
        margin-right: 12px;
    }

    .buttons span {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }

    .red { background: #ff5f57; }
    .yellow { background: #ffbd2e; }
    .green { background: #28c840; }

    .address-bar {
        flex: 1;
        background: #f3f3f3;;
        border-radius: 8px;
        height: 20px;
        display: flex;
        align-items: center;
        padding: 0 10px;
        font-size: 12px;
        color: #555;
        justify-content: center;
    }


    .browser-content {
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

</style>
