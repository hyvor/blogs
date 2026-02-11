import consoleApi from "../../../../lib/consoleApi";
import type { Export } from "../../../../lib/types";

export function startExport() {
  return consoleApi.post<Export>({
    endpoint: "/data/export",
  });
}

export function getExports() {
  return consoleApi.get<Export[]>({
    endpoint: "/data/exports",
  });
}
