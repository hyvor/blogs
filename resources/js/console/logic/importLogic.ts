import {kea, key, path, props} from "kea";
import {ajax} from "kea-ajax";
import api from "../lib/api";
import type { importLogicType } from "./importLogicType";

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

    ajax(({actions, values, props}) => ({

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

    })),

]);