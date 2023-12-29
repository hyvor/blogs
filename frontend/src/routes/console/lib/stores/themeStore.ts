import { derived, writable } from "svelte/store";
import type { ThemeFile } from "../types";
import consoleApi from "../consoleApi";

export const themeFilesOriginalStore = writable<ThemeFile[]>([]);
export const themeFilesStore = writable<ThemeFile[]>([]);

export const selectedThemeFileIdStore = writable<number | null>(null);

export const selectedThemeFileStore = derived(
    [themeFilesStore, selectedThemeFileIdStore],
    ([$themeFiles, $selectedThemeFileId]) => {
        return $themeFiles.find(themeFile => themeFile.id === $selectedThemeFileId);
    }
)

export const selectedThemeFileOriginalStore = derived(
    [themeFilesOriginalStore, selectedThemeFileIdStore],
    ([$themeFiles, $selectedThemeFileId]) => {
        return $themeFiles.find(themeFile => themeFile.id === $selectedThemeFileId);
    }
)

export function loadThemeFiles() {
    return consoleApi.get<ThemeFile[]>({
        endpoint: '/theme/files'
    });
}

export function updateThemeFileStore(id: number, data: Partial<ThemeFile>, original = false) {
    const stores = [themeFilesStore]
    if (original) {
        stores.push(themeFilesOriginalStore);
    }

    stores.forEach(store => {
        store.update(themeFiles => {
            return themeFiles.map(themeFile => {
                if (themeFile.id === id) {
                    return {
                        ...themeFile,
                        ...data,
                    }
                }

                return themeFile;
            });
        })
    })
}

export function removeThemeFileStore(id: number) {
    themeFilesStore.update(themeFiles => {
        return themeFiles.filter(themeFile => themeFile.id !== id);
    })
}