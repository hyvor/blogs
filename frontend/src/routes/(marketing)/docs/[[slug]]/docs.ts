import type { ComponentType } from "svelte";
import Introduction from "./content/Introduction.svelte";
import Writing from "./content/writing/Writing.svelte";
import CustomDomain from "./content/custom-domain/CustomDomain.svelte";
import SubDirectoryHosting from "./content/subdirectory/SubDirectoryHosting.svelte";
import Services from "./content/services/Services.svelte";
import Terms from "./content/terms/Terms.svelte";
import PrivacyPolicy from "./content/privacy-policy/PrivacyPolicy.svelte";
import Seo from "./content/seo/Seo.svelte";
import Fonts from "./content/fonts/Fonts.svelte";
import Redirect from "./content/redirect/Redirect.svelte";
import Media from "./content/media/Media.svelte";
import Routes from "./content/routes/Routes.svelte";
import CustomCode from "./content/custom-code/CustomCode.svelte";
import Languages from "./content/languages/Languages.svelte";
import Export from "./content/export/Export.svelte";
import ImportSitemap from "./content/import-sitemap/ImportSitemap.svelte";
import Webhooks from "./content/webhooks/Webhooks.svelte";
import ApiDelivery from "./content/api-delivery/ApiDelivery.svelte";

export const categories: Category[] = [

    {
        name: 'Intro',
        pages: [

            {
                slug: '',
                name: 'Introduction',
                component: Introduction
            },

            {
                slug: 'writing',
                name: 'Writing',
                component: Writing,
            }
        ]
    },

    {
        name: 'Hosting',
        pages: [
            {
                slug: 'custom-domain',
                name: 'Custom  Domain',
                component: CustomDomain,
            },
            {
                slug: 'subdirectory',
                name: 'Subdirectory',
                component: SubDirectoryHosting,
            }
        ]
    },

    {
        name: 'Features',
        pages: [
            {
                slug: 'languages',
                name: 'Languages',
                component: Languages,
            },
            {
                slug: 'media',
                name: 'Media',
                component: Media,
            },
            {
                slug: 'fonts',
                name: 'Fonts',
                component: Fonts,
            },
            {
                slug: 'seo',
                name: 'SEO',
                component: Seo
            },
            {
                slug: 'redirects',
                name: 'Redirects',
                component: Redirect,
            },
            {
                slug: 'custom-code',
                name: 'Custom Code',
                component: CustomCode
            },
            {
                slug: 'services',
                name: 'Services',
                component: Services
            },
            {
                slug: 'routes',
                name: 'Routes',
                component:Routes
            },
            {
                slug: 'navigation',
                name: 'Navigation',
                component: Routes
            }
        ]
    },

    {
        name: 'Developer',
        pages: [
            {
                slug: 'webhooks',
                name: 'Webhooks',
                component: Webhooks,
            },
            {
                slug: 'api-data',
                name: 'Data API',
                component: Webhooks,
            },
            {
                slug: 'api-console',
                name: 'Console API',
                component: Webhooks,
            },
            {
                slug: 'api-delivery',
                name: 'Delivery API',
                component: ApiDelivery,
            }
        ],
    },

    {
        name: 'Data',
        pages: [
            {
                slug: 'export',
                name: 'Export Data',
                component: Export,
            },
            {
                slug: 'import-sitemap',
                name: 'Import from Sitemap',
                component: ImportSitemap,
            }
        ]
    },

    {
        name: 'Legal',
        pages: [
            {
                slug: 'terms',
                name: 'Terms',
                component: Terms,
            },
            {
                slug: 'privacy-policy',
                name: 'Privacy Policy',
                component: PrivacyPolicy,
            }
        ]
    },   
    
];


export const pages = categories.reduce((acc, category) => acc.concat(category.pages), [] as Page[]);

interface Category {
    name: string,
    pages: Page[]
}

interface Page {
    slug: string,
    name: string,
    component: ComponentType
}