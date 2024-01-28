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
import Overview from "./content/overview/Overview.svelte";
import Templates from "./content/templates/Templates.svelte";
import Styling from "./content/styling/Styling.svelte";
import Scripts from "./content/scripts/Scripts.svelte";

export const categories = [

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
        name: 'Features',
        pages: [
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
                slug: 'media',
                name: 'Media',
                component: Media,
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
        name: 'Theme Development',
        pages: [
            {
                slug: 'overview',
                name: 'Overview',
                component: Overview
            },

            {
                slug: 'templates',
                name: 'Templates',
                component: Templates
            },

            {
                slug: 'styling',
                name: 'Styling',
                component:Styling
            },

            {
                slug: 'scripts',
                name: 'Scripts',
                component: Scripts
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
    
    
] as Category[];


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