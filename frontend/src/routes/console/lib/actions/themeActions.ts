import consoleApi from "../consoleApi";
import type { ThemeFile } from "../types";


export function updateFile(id: number, data: Partial<ThemeFile>) {
    return consoleApi.patch({
        endpoint: '/theme/file/' + id,
        data
    })
}