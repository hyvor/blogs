import { derived, writable } from "svelte/store";
import type { ThemeFile } from "../../../lib/types";
import consoleApi from "../../../lib/consoleApi";

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

export const configModeStore = writable<'yaml' | 'ui'>('ui');

export function setThemeFiles(files: ThemeFile[]) {
    themeFilesStore.set(files);
    themeFilesOriginalStore.set(files);

    // focus the file
    selectedThemeFileIdStore.set(getFocusFileId(files));

    // get the first file to focus
    function getFocusFileId(files: ThemeFile[]) {
        const configYaml = files.find(file => file.folder === null && file.name === 'config.yaml');
        if (configYaml) return configYaml.id;

        const firstRoot = files.find(file => file.folder === null);
        if (firstRoot) return firstRoot.id;

        return null;
    }

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
    [themeFilesStore, themeFilesOriginalStore].forEach(store => {
        store.update(themeFiles => {
            return themeFiles.filter(themeFile => themeFile.id !== id);
        })
    });
}

export function addThemeFileToStore(themeFile: ThemeFile) {
    [themeFilesStore, themeFilesOriginalStore].forEach(store => {
        store.update(themeFiles => {
            return [
                ...themeFiles,
                themeFile
            ]
        })
    });
}