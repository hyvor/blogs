import consoleApi from "../../../lib/consoleApi";
import type { Redirect } from "../../../lib/types";


export function createRedirect(path: string, to: string, type: 'temporary' | 'permanent') {
    return consoleApi.post<Redirect>({
        endpoint: '/redirect',
        data: {path, to, type}
    })
}

export function updateRedirect(id: number, path: string, to: string, type: 'temporary' | 'permanent') {
    return consoleApi.patch<Redirect>({
        endpoint: `/redirect/${id}`,
        data: {path, to, type}
    })
}

export function deleteRedirect(id: number) {
    return consoleApi.delete({
        endpoint: `/redirect/${id}`
    })
}