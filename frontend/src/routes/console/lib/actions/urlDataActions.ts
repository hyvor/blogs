import consoleApi from "../consoleApi";
import type { UrlData } from "../types";


export function getUrlData(url: string, type: 'embed' | 'link') {

    return consoleApi.get<UrlData>({
        endpoint: '/url-data',
        data: {
            url,
            type
        }
    });

}