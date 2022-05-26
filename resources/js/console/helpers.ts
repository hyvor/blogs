import {appConfig, ConsoleWindow} from "./types";

export function appConfig() : appConfig {
    return (window as ConsoleWindow).appConfig;
}