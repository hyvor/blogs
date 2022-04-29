import {appConfig} from "./objects/appConfig";

export type ConsoleWindow = (typeof window) & {
    appConfig: appConfig
}