import consoleApi from "../consoleApi";
import type { ThemeFile, ThemeFolder } from "../types";


export function updateFile(id: number, data: Partial<ThemeFile>) {
    return consoleApi.patch({
        endpoint: '/theme/file/' + id,
        data
    })
}

export function deleteFile(id: number) {
    return consoleApi.delete({
        endpoint: '/theme/file/' + id
    })
}


export function checkFilename(
    name: string, 
    folder: ThemeFolder,
    signal?: AbortSignal
) {
    return consoleApi.get<{available: boolean}>({
        endpoint: '/theme/file/name-available',
        data: {
            name,
            folder
        },
        signal
    })
}