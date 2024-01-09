import consoleApi from "../../../lib/consoleApi";


export function getRoutes() {
    return consoleApi.get({
        endpoint: '/routes',
    });
}