<script lang="ts">
    import logoAqeez from '$lib/img/customers/aqeez.svg';
    import logoSubdivisions from '$lib/img/customers/subdivisions.svg';
    import logoTimesOfMadeira from '$lib/img/customers/timesofmadeira.svg';
    import logoBeautyVouch from '$lib/img/customers/beautyvouch.png';
    import logoJobPosts from '$lib/img/customers/jobposts.webp';
    import yogakiddy from '$lib/img/customers/yogakiddy.png';
    import logoUniverbal from '$lib/img/customers/univerbal.svg';

    import { Button } from '@hyvor/design/components';
    import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
    import Review from "./Review.svelte";

    interface Props {
        button?: boolean;
    }

    let { button = true }: Props = $props();

    const customers = [
        {
            name: 'Aqeez',
            logo: logoAqeez,
            url: 'https://blog.aqeez.com'
        },
        {
            name: 'Times of Madeira',
            logo: logoTimesOfMadeira,
            url: 'https://timesofmadeira.com',
            height: 65
        },
        {
            name: 'Subdivisions',
            logo: logoSubdivisions,
            url: 'https://blog.subdivisions.com'
        },
        {
            name: 'Job Posts',
            logo: logoJobPosts,
            url: 'https://jobposts.ai/blog',
            width: 120,
            height: 30
        },
        {
            name: 'Yoga Kiddy',
            logo: yogakiddy,
            url: 'https://fr.yogakiddy.com',
            width: 40,
            height: 40
        },
        {
            name: 'Univerbal',
            logo: logoUniverbal,
            url: 'https://blog.univerbal.app',
            width: 130,
            height: 50
        }
    ];

    // const reviews = [
    //     {
    //         name: 'Lionel S.',
    //         role: 'Blogger',
    //         review:
    //             'I need a simple, easy-to-use, fast, beautiful and mature blogging tool that resolves the WordPress bloat. Hyvor Blogs handles this beautifully.'
    //     },
    //     {
    //         name: 'Manoj P.',
    //         role: 'Senior Application Engineer',
    //         review:
    //             ' The platform offers a seamless and user-friendly experience for both bloggers and readers. The customization options are extensive, allowing bloggers to create a unique and visually appealing blog.'
    //     }
    // ];
</script>

<div class="wrap">
    <h3>Loved by 100+ bloggers & businesses</h3>

    <div class="customer-logos">
        {#each customers as customer}
            <a href={customer.url} target="_blank" rel="nofollow noopener">
                <img
                    src={customer.logo}
                    alt={customer.name}
                    width={customer.width || 140}
                    height={customer.height || 30}
                />
            </a>
        {/each}
    </div>

    <div class="reviews">
        <Review
            userName="Lionel S."
            country="France",
            title="Simple and easy-to-use blogging tool"
            date="2023-08-15"
            stars={5}
            review="I need a simple, easy-to-use, fast, beautiful and mature blogging tool that resolves the WordPress bloat. Hyvor Blogs handles this beautifully."
            verified={true}
            />

        <Review
            userName="Manoj P."
            country="India",
            title="Seamless and user-friendly experience"
            date="2023-09-10"
            stars={5}
            review="The platform offers a seamless and user-friendly experience for both bloggers and readers. The customization options are extensive, allowing bloggers to create a unique and visually appealing blog."
            verified={true}
            />
        <Review
            userName="Sophia L."
            country="USA",
            title="Excellent customer support"
            date="2023-07-22"
            stars={5}
            review="The customer support team is responsive and helpful. They assisted me in setting up my blog and resolving any issues I encountered along the way."
            verified={true}
            />

    </div>

    {#if button}
        <div class="view-on-g2">
            <Button
                as="a"
                color="gray"
                variant="fill"
                href="https://www.g2.com/products/hyvor-blogs/reviews"
                target="_blank"
            >
                View reviews on G2
                {#snippet end()}
                    <IconBoxArrowUpRight size={12} />
                {/snippet}
            </Button>
        </div>
    {/if}
</div>

<style lang="scss">
    .wrap {
        padding: 60px 15px;
    }
    h3 {
        text-align: center;
        font-size: 26px;
    }
    .customer-logos {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px 0;
        flex-wrap: wrap;
        gap: 30px;
    }

    .reviews {
        margin: 10px 0;
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 0 50px;
        scroll-padding: 50px;
    }

    .view-on-g2 {
        text-align: center;
    }

    @media (max-width: 992px) {
        .reviews {
            flex-direction: column;
            padding: 0;
            .review {
                width: 100%;
                padding: 15px;
            }
        }
    }
</style>
