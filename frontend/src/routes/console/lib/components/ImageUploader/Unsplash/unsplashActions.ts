import consoleApi from "../../../consoleApi";
import type { UnsplashImage } from "../../../types";


export function searchUnsplash(search: string, page: number) {
    return consoleApi.get<UnsplashImage[]>({
        endpoint: '/media/unsplash/search',
        data: {
            search,
            page
        }
    })
}