import {ConsoleWindow} from "./types";
import {appConfig as appConfigType} from "./objects/appConfig";

export function appConfig() : appConfigType {
    return (window as ConsoleWindow).appConfig;
}