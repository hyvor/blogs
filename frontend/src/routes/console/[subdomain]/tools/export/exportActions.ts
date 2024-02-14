import consoleApi from "../../../lib/consoleApi";
import type { Export } from "../../../lib/types";

export type ExportFormat = 'hyvor_blogs' | 'wordpress';

export function startExport(format: ExportFormat) {
    return consoleApi.post<Export>({
        endpoint: '/data/export'
    })
}

export function getExports() {
    return consoleApi.get<Export[]>({
        endpoint: '/data/exports'
    })
}