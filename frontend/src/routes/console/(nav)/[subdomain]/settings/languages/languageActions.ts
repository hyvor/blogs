import consoleApi from "../../../../lib/consoleApi";
import type { Language } from "../../../../lib/types";


export function createLanguage(name: string, code: string, direction: 'ltr' | 'rtl') {
    return consoleApi.post<Language>({
        endpoint: '/language',
        data: {name, code, direction}
    })
}

export function updateLanguage(id: number, name: string, code: string, direction: 'ltr' | 'rtl') {
    return consoleApi.patch<Language>({
        endpoint: `/language/${id}`,
        data: {name, code, direction}
    })
}

export function deleteLanguage(id: number) {
    return consoleApi.delete({
        endpoint: `/language/${id}`
    })
}