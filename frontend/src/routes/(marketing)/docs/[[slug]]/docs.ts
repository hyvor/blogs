import type { ComponentType } from "svelte";
import Introduction from "./content/Introduction.svelte";
import Writing from "./content/writing/Writing.svelte";
import CustomDomain from "./content/custom-domain/CustomDomain.svelte";
import SubDirectoryHosting from "./content/subdirectory/SubDirectoryHosting.svelte";

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
    }
    
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