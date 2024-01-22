<script lang="ts">
	import { Tag } from "@hyvor/design/components";

    type CustomerType = 'personal' | 'business' | 'news';

    interface Customer {
        image: string,
        name: string,
        type: CustomerType,
        url: string,
        country?: string,
        color?: string,
    }


    const customers: Customer[] = [
        {
            image: 'hyvorblog_masfzs.webp',
            type: 'business',
            name: 'HYVOR Blog',
            url: 'hyvor.com/blog',
            country: '🇫🇷',
        },
        {
            name: 'Wild Workflows',
            type: 'business',
            image: 'wildworkflows-blog.webp',
            url: 'blog.wildworkflows.com',
            country: '🇬🇧',
            color: '#ebeae6'
        },
        {
            name: 'Riccardo Padovani',
            type: 'personal',
            image: 'rpadovani.webp',
            url: 'rpadovani.com',
            country: '🇩🇪',
            color: '#fff0ea'
        },
        {
            name: 'Sami Ramly',
            type: 'personal',
            image: 'samiramly_cqakpw.webp',
            url: 'samiramly.com',
            country: '🇺🇸',
            color: '#ffece4'
        },
        {
            name: 'Metamoonshots',
            type: 'business',
            image: 'moonshots-blog_vwlnpk.webp',
            url: 'blog.metamoonshots.io',
            country: '🇮🇳',
            color: '#fee8ff',
        },
        {
            name: 'Didgii',
            type: 'business',
            image: 'didgii-blog_yuhiqv.webp',
            url: 'blog.didgii.com',
            country: '🇲🇽',
            color: '#e9f6ff'
        },
        {
            name: 'Deflekt',
            type: 'business',
            image: 'deflekt_ik0u1p.webp',
            url: 'deflekt.ai/blog',
            country: '🇳🇱',
            color: '#f2ebff'
        },
        {
            name: 'David Hooper',
            type: 'personal',
            image: 'davidhooper_zb90qt.webp',
            url: 'davidhooper.net',
            country: '🇺🇸',
            color: '#efefef',
        },
        {
            name: 'Conservatruth Blog',
            type: 'news',
            image: 'consevatruth-blog_kglswp.webp',
            url: 'conservatruthblog.com',
            country: '🇺🇸',
            color: '#ffe9e5'
        },
        {
            name: 'Warm and Wise',
            type: 'personal',
            image: 'eiching.webp',
            url: 'warmandwise.com',
            country: '🇹🇼'
        },
        {
            name: 'Praca ze smakiem',
            type: 'business',
            image: 'pracazesmakiem_ctcqtv.webp',
            url: 'pracazesmakiem.pl',
            country: '🇵🇱',
            color: '#d6e7f9',
        }
    ];

    function getTagColor(type: CustomerType) {
        switch (type) {
            case 'business':
                return 'blue';
            case 'personal':
                return 'green';
            case 'news':
                return 'orange';
        }
    }

</script>

<div class="wrap">

    {#each customers as customer}

        <a 
            class="customer"
            style:background-color={customer.color || '#eee'}
            href="//{customer.url}" 
            target="_blank" 
            rel="nofollow"
        >
            <img src="https://res.cloudinary.com/dqabfne6s/image/upload/c_scale,w_800/v1687716078/blogs.hyvor.com/customer-screenshots/{customer.image}" alt="{customer.url}">

            <div class="data">

                <div class="name-wrap">
                    <div class="name">{customer.name} {customer.country || ''}</div>
                    <div class="url">
                        {customer.url}
                    </div>
                </div>
                
                <div class="type">
                    <Tag color={getTagColor(customer.type)}>{customer.type.toUpperCase()}</Tag>
                </div>

            </div>

        </a>

    {/each}

</div>

<style lang="scss">

    .wrap {
        width: 100%;
        padding: 0 20px;
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: 16px;
    }

    .customer {
        width: calc(50% - 8px);
        position: relative;
        padding: 30px;
        border-radius: 20px;
        transition: .1s;
        &:hover {
            transform: translateY(-10px);
        }

        img {
            max-width: 100%;
            border-radius: 20px;
            aspect-ratio: 16 / 9;
            transition: .2s ease-in-out;
            &:hover {
            }
        }

        .data {
            margin-top: 20px;
            display: flex;
            align-items: center;
        }

        .name-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .name {
            font-weight: 600;
            font-size:18px;
        }

        .url {
            margin-top: 1px;
            color: var(--text-light);
        }

        @media screen and (max-width: 920px) {
            width: 100%;
        }
    }

</style>