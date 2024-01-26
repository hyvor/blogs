import type { ComponentType } from "svelte";
import Introduction from "./content/Introduction.svelte";
import Writing from "./content/writing/Writing.svelte";
import CustomDomain from "./content/custom-domain/CustomDomain.svelte";
import SubDirectoryHosting from "./content/subdirectory/SubDirectoryHosting.svelte";
import Services from "./content/services/Services.svelte";
import { TabNav } from "@hyvor/design/components";
import Terms from "./content/terms/Terms.svelte";
import PrivacyPolicy from "./content/prrivacy-policy/PrivacyPolicy.svelte";

export const categories = [

    {
        name: 'Intro',
        items: [

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
        items: [

            {
                slug: 'services',
                name: 'Services',
                component: Services
            }
        ]

    },

    {
        name: 'Hosting',
        items: [
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
        name: 'Legal',
        items: [
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
    }
    
    
] as Category[];


export const items = categories.reduce((acc, category) => {
    return acc.concat(category.items);
}, [] as Item[]);

interface Category {
    name: string,
    items: Item[]
}

interface Item {
    slug: string,
    name: string,
    component: ComponentType
}