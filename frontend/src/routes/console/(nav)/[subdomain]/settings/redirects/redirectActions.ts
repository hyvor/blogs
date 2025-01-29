import consoleApi from "../../../../lib/consoleApi";
import type { Redirect } from "../../../../lib/types";

interface GetRedirectProps {
    search?: string,
    limit?: number,
    offset?: number
}

export function getRedirect({search, limit, offset} : GetRedirectProps = {}) {
    return consoleApi.get<Redirect[]>({
        endpoint: '/redirects',
        data: {
            search,
            limit,
            offset
        }
    })
}

export function createRedirect(dynamic: boolean, path: string, to: string, type: 'temporary' | 'permanent') {
    return consoleApi.post<Redirect>({
        endpoint: '/redirect',
        data: {dynamic, path, to, type}
    })
}

export function updateRedirect(id: number, dynamic: boolean, path: string, to: string, type: 'temporary' | 'permanent') {
    return consoleApi.put<Redirect>({
        endpoint: `/redirect/${id}`,
        data: {dynamic, path, to, type}
    })
}

export function deleteRedirect(id: number) {
    return consoleApi.delete({
        endpoint: `/redirect/${id}`
    })
}