<script lang="ts">
    import {integrations} from "../../../integrations/integrations";
    import { Button, Tag } from '@hyvor/design/components';

    //to find free integrations
    const hyvorTalk = integrations.find(integration => integration.slug === 'hyvor-talk-comments');
    const hyvorPost = integrations.find(integration => integration.slug === 'hyvor-post');

</script>

<div class="above-fold" id="seo-section">
    <div class="title">
        <h1>
            Integrate with Your Favorite Tools
        </h1>
        <h2>
            Add custom codes to your site to connect with analytics, marketing, and other third-party tools.
        </h2>
    </div>


</div>
<div class="view-more">
    <div class="free" style="display: flex; gap: 20px;">

        <div class="free">
            <div class="hyvor-card">
                <div class="hyvor-talk-section">
                    <img src={hyvorTalk.icon} alt="Hyvor Talk" class="integration-icon" width="70px">
                    <div class="name-wrapper">
                        <div class="name" style="font-size: 24px">{hyvorTalk.name}</div>
<!--                        <div class="subname">{hyvorTalk.subname}</div>-->
                        <p style="font-size: 20px">Add a commenting space to your blog</p>
                    </div>
                </div>
                <div class="diagonal-divider"></div>
                <div class="hyvor-post-section">
                    <div class="subname"><Tag size="small" color="blue">{hyvorPost.subname}</Tag></div>
                    <img src={hyvorPost.icon} alt="Hyvor Post" class="integration-icon" width="70px">
                    <div class="name-wrapper">
                        <div class="name" style="font-size: 24px">{hyvorPost.name}</div>
<!--                        <Tag size="small" color="blue">{hyvorPost.subname}</Tag>-->
                        <p style="font-size: 20px">Boost your content marketing with Hyvor Post</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<div class="integration-container">
    <div class="scrolling-wrapper">
        <div class="scrolling-content">
            {#each integrations as integration}
                <div class="integration-item">
                    <div
                        class="overlay"
                        style:background-color={integration.color}
                    ></div>
                    <img
                        src="{integration.icon}"
                        alt="{integration.name}"
                        class="integration-icon"
                    >
                    <div class="name-wrapper">
                        <div class="name">{integration.name}</div>
                        {#if integration.subname}
                            <div class="subname">{integration.subname}</div>
                        {/if}
                    </div>
                </div>
            {/each}
            <!-- Duplicate the integrations for seamless loop -->
            {#each integrations as integration}
                <div class="integration-item">
                    <div
                        class="overlay"
                        style:background-color={integration.color}
                    ></div>
                    <img
                        src="{integration.icon}"
                        alt="{integration.name}"
                        class="integration-icon"
                    >
                    <div class="name-wrapper">
                        <div class="name">{integration.name}</div>
                        {#if integration.subname}
                            <div class="subname">{integration.subname}</div>
                        {/if}
                    </div>
                </div>
            {/each}
        </div>
    </div>
</div>

<style lang="scss">
    .free {
        display: flex;
        justify-content: center;


        .hyvor-card {
            width: 97vw;
            height: 300px; // Increased from 200px
            position: relative;
            overflow: hidden;
            border-radius: 20px;

            .hyvor-talk-section,
            .hyvor-post-section {
                min-height: 300px; // Increased from 200px
                position: relative;
                width: calc(50% - 30px);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 20px;

                .integration-icon {
                    max-width: 40px;
                    max-height: 40px;
                    margin-bottom: 10px;
                }

                .name-wrapper {
                    text-align: center;

                    .name {
                        font-size: 18px;
                        font-weight: bold;
                        margin: 0 0 5px 0;
                        color: #333;
                    }
                }
            }

            .hyvor-talk-section {
                float: left;
                background: #fff2e6;

                &:after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 100%;
                    width: 0;
                    height: 0;
                    border-top: 300px solid  #fff2e6;
                    border-right: 50px solid transparent;
                }
            }

            .hyvor-post-section {
                float: right;
                background: #e8f4fd;

                &:before {
                    content: '';
                    position: absolute;
                    top: 0;
                    right: 100%;
                    width: 0;
                    height: 0;
                    border-bottom: 300px solid #e8f4fd;
                    border-left: 50px solid transparent;
                }
            }
        }
    }

    .free .subname {
        position: absolute;
        top: 20px;
        right: 20px;

    }

    .free img {
        max-width: 70px !important;
        max-height:  70px !important;
    }

    .view-more {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-top: 50px;

    }

    :global(body) {
        overflow-x: hidden;
    }

    .above-fold {
        display: flex;
        padding-top: 75px;
        flex-direction: column;
    }

    .title {
        text-align: end;
        //margin-bottom: 40px;
        padding-right: 50px;
        padding-left: 75px;
    }

    .title h1 {
        font-size: 60px !important;
        margin: 0 !important;
    }

    .title h2 {
        font-size: 20px !important;
        //margin: 0 !important;
        font-weight: normal;
    }

    .integration-container {
        overflow: hidden;
        margin-bottom: 20px;
        width: 100%;
        margin-top: 10px;
    }

    .scrolling-wrapper {
        overflow: hidden;
        width: 100%;
    }

    .scrolling-content {
        display: flex;
        animation: scroll 30s linear infinite;
        gap: 20px;
        width: max-content;
        padding: 20px 0;
    }

    @keyframes scroll {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }

    .integration-item {
        flex: 0 0 auto;
        width: 200px;
        padding: 20px 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 12px;
        text-align: center;
        cursor: pointer;
        transition: 0.3s transform;
        position: relative;
        overflow: hidden;
        border-radius: 20px;

        &:hover {
            transform: translateY(-5px);
            animation-play-state: paused;
        }

        .integration-icon {
            max-width: 50px;
            max-height: 50px;
            object-fit: contain;
        }

        .name-wrapper {
            z-index: 1;
        }

        .name {
            margin-top: 20px;
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        .subname {
            font-size: 13px;
            color: var(--text-light);
            margin-top: 2px;
        }

        .overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            opacity: 0.3;
            border-radius: 15px;
        }
    }

    /* Pause animation on hover */
    .scrolling-wrapper:hover .scrolling-content {
        animation-play-state: paused;
    }


    /* media queries */
    @media (max-width: 768px) {
        .title {
            text-align: center;
            padding-right: 20px;
            padding-left: 20px;
        }

        .title h1 {
            font-size: 40px !important;
        }

        .integration-item {
            min-width: 160px;
        }

        .scrolling-content {
            animation-duration: 25s;
        }
    }
</style>
