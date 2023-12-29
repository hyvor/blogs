import consoleApi from "../consoleApi";
import type { ThemeFile, ThemeFolder } from "../types";

export function createFile(folder: ThemeFolder, name: string, content: string | Blob = '') {
    const formData = new FormData()
    formData.append('name', name)
    formData.append('folder', folder || '');
    formData.append(content instanceof Blob ? 'file' : 'content', content)

    console.log(formData.get('name'));

    return consoleApi.post<ThemeFile>({
        endpoint: '/theme/file',
        data: formData
    });
}

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