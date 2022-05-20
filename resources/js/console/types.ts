import {appConfig} from "./objects/appConfig";

export type ConsoleWindow = (typeof window) & {
    appConfig: appConfig
}

export interface Filters {
    status: string,
    author: string | number,
    tag: number | null,
    startDate: Date | null,
    endDate: Date | null,
    search: string
}

