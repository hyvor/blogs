import consoleApi, { APP_URL } from "../../lib/consoleApi";
import type { Theme, ThemeFile, ThemeFolder } from "../../lib/types";

export function loadThemeFiles() {
    return consoleApi.get<ThemeFile[]>({
        endpoint: '/theme/files'
    });
}

export async function loadThemes() {
    const response = await fetch(APP_URL + '/api/special/themes');

    if (!response.ok) {
        throw new Error('Failed to load themes');
    }

    const json = await response.json();
    return json as Theme[];
}

export function changeTheme(name: string) {
    return consoleApi.patch<ThemeFile[]>({
        endpoint: '/theme',
        data: {name}
    });
}

export function updateFile(id: number, data: Partial<ThemeFile>) {
    return consoleApi.patch<ThemeFile>({
        endpoint: '/theme/file/' + id,
        data
    })
}



export function deleteFile(id: number) {
    return consoleApi.delete({
        endpoint: '/theme/file/' + id
    })
}

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

export function uploadTheme(zip: File) {

    const formData = new FormData()
    formData.append('zip', zip)

    return consoleApi.post<ThemeFile[]>({
        endpoint: '/theme',
        data: formData
    });
}