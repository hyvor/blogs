import { derived, writable } from "svelte/store";
import type { ThemeFile } from "../types";
import consoleApi from "../consoleApi";

export const themeFilesStore = writable<ThemeFile[]>([]);

export const selectedThemeFileIdStore = writable<number | null>(null);

export const selectedThemeFileStore = derived(
    [themeFilesStore, selectedThemeFileIdStore],
    ([$themeFiles, $selectedThemeFileId]) => {
        return $themeFiles.find(themeFile => themeFile.id === $selectedThemeFileId);
    }
)


export function loadThemeFiles() {
    return consoleApi.get<ThemeFile[]>({
        endpoint: '/theme/files'
    });
}