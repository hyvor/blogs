import consoleApi from "../../../../lib/consoleApi";
import type { Navigation, NavigationVariant } from "../../../../lib/types";

interface GetRedirectProps {
    limit?: number,
    offset?: number
}

export function getNavigations({limit, offset} : GetRedirectProps = {}) {
    return consoleApi.get<Navigation[]>({
        endpoint: '/navigations',
        data: {
            limit,
            offset
        }
    })
}

export function createNavigation(name: string, url: string, type: 'header' | 'footer') {
    return consoleApi.post<Navigation>({
        endpoint: '/navigation',
        data: {name, url, type}
    })
}

export function createNavigationVariant(id: number, languageId: number) {
    return consoleApi.post<NavigationVariant>({
        endpoint: `/navigation/${id}/variant`,
        data: {
            language_id: languageId
        }
    })
}

export function updateNavigationVariant(navigationId: number, languageId: number, variant: Partial<NavigationVariant>, type: 'header' | 'footer', url: string) {
    return consoleApi.patch<NavigationVariant>({
        endpoint: `/navigation/${navigationId}/variant`,
        data: {
            language_id: languageId,
            ...variant
        }
    })
}

export function updateNagivation(id: number, navigation: Partial<Navigation>) {
    return consoleApi.patch<Navigation>({
        endpoint: `/navigation/${id}`,
        data: {...navigation}
    })
}

export function deleteNavigation(id: number) {
    return consoleApi.delete({
        endpoint: `/navigation/${id}`
    })
}

export function saveSort(ids: number[]) {
    return consoleApi.patch({
        endpoint: '/navigations/sort',
        data: {ids},
    })
}