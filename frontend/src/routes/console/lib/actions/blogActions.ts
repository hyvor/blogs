import consoleApi from "../consoleApi";



export function saveSort(ids: number[]) {
    return consoleApi.patch({
        endpoint: '/blogs/sort',
        data: {blog_ids: ids}
    })
}