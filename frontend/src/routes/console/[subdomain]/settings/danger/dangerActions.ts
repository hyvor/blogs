import consoleApi from "../../../lib/consoleApi"


type ClearCacheData = {
    type: 'all' | 'template'
}  | {
    type: 'paths',
    paths: string[]
}

export function clearBlogCache(data: ClearCacheData) {
    return consoleApi.delete({
        endpoint: '/blog/cache',
        data
    })
}

export function deleteBlogDangerous() {
    return consoleApi.delete({
        endpoint: '/blog'
    })
}