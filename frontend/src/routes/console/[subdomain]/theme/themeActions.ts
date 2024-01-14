import consoleApi, { APP_URL } from "../../lib/consoleApi";
import type { Theme, ThemeFile } from "../../lib/types";

export function loadThemeFiles() {
    return consoleApi.get<ThemeFile[]>({
        endpoint: '/theme/files'
    });
}

export async function loadThemes() {
    const response = await fetch(APP_URL + '/api/special/themes');
    const json = await response.json();
    return json as Theme[];
}