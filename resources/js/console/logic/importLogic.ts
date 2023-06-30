import {actions, kea, key, path, props, reducers} from "kea";
import {ajax} from "kea-ajax";
import api from "../lib/api";
import type { importLogicType } from "./importLogicType";
import {Import} from "../types";

export type SitemapDataSelectType = 'meta_tag' | 'css_selector';

export interface SitemapTestInput {
    url: string,
    slug_exclude: string,
    css: {
        title: string,
        description: string,
        content: string,
        content_exclude: string,
        published_date: string
    }
}

export type SitemapImportInput = Omit<SitemapTestInput, 'url'> & {
    sitemap_url: string,
    import_images: boolean,
}

export interface SitemapTestResponse {
    url: string,
    data: {
        title: string,
        description: string,
        content: string,
        content_html: string,
        published_at: number,
        featured_image_url: string | null,
        slug: string
    },
    meta: {
        select_type: {
            title: SitemapDataSelectType,
            description: SitemapDataSelectType,
            published_at: SitemapDataSelectType
        }
    }
}

export const importLogic = kea<importLogicType>([

    props({} as {subdomain: string}),
    key((props) => props.subdomain),
    path((key) => ['import', key]),

    actions(({values}) => ({
        setImports: (imports: Import[]) => ({imports}),
        addImport: (i: Import) => ({i}),
    })),

    ajax(({actions, values, props}) => ({

        getImports: async () => {
            const data = await api.get<Import[]>(props.subdomain, '/data/imports');
            actions.setImports(data);
        },

        sitemapTest: async ({ input, onLoad, onError } : {
            input: SitemapTestInput,
            onLoad: (r: SitemapTestResponse) => void,
            onError: Function
        }) => {

            try {
                const data = await api.post<SitemapTestResponse>(
                    props.subdomain,
                    '/data/import/sitemap/test',
                    input
                );
                onLoad(data);

            } catch (e) {
                onError(e);
            }

        },

        sitemapImport: async ({ input, onLoad } : { input: SitemapImportInput, onLoad: (i: Import) => void }) => {
            const i = await api.post<Import>(
                props.subdomain,
                '/data/import/sitemap/import',
                input
            );
            actions.addImport(i);
            onLoad(i);
        }

    })),

    reducers({

        imports: [
            [] as Import[],
            {
                setImports: (_, {imports}) => imports,
                addImport: (state, {i}) => [...state, i]
            }
        ]

    })

]);