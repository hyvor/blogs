import consoleApi from "../../../lib/consoleApi";
import type { Tag } from "../../../lib/types";


interface GetTagsProps {
    limit?: number,
    offset?: number
}

export function getTags({limit, offset} : GetTagsProps = {}) {
    return consoleApi.get<Tag[]>({
        endpoint: '/tags',
        data: {
            limit,
            offset
        }
    })
}