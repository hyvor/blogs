import consoleApi from "../../../lib/consoleApi";
import type { Import } from "../../../lib/types";


export function getImports() {
    return consoleApi.get<Import[]>({
        endpoint: '/data/imports',
    })
}

export interface TestSitemapData {
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

export type SitemapDataSelectType = 'meta_tag' | 'css_selector';

export interface TestSitemapResponse {
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

export function testSitemapUrl(data: TestSitemapData) {
    return consoleApi.post<TestSitemapResponse>({
        endpoint: '/data/import/sitemap/test',
        data
    })
}

export type ImportSitemapData = Omit<TestSitemapData, 'url'> & {
    sitemap_url: string,
    import_images: boolean,
}


export function importSitemap(data: ImportSitemapData) {
    return consoleApi.post<Import>({
        endpoint: '/data/import/sitemap/import',
        data
    })
}