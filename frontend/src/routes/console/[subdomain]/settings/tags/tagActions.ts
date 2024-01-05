import consoleApi from "../../../lib/consoleApi";
import type { TagVariant, Tag } from "../../../lib/types";


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

export function createTag(name: string) {
    return consoleApi.post<Tag>({
        endpoint: '/tag',
        data: {
            name
        }
    })
}

export function deleteTag(id: number) {
    return consoleApi.delete({
        endpoint: `/tag/${id}`
    })
}

export function createTagVariant(id: number, languageId: number) {
    return consoleApi.post<TagVariant>({
        endpoint: `/tag/${id}/variant`,
        data: {
            language_id: languageId
        }
    })
}

export function updateTagVariant(tagId: number, languageId: number, variant: Partial<TagVariant>) {
    return consoleApi.patch<TagVariant>({
        endpoint: `/tag/${tagId}/variant`,
        data: {
            language_id: languageId,
            ...variant
        }
    })
}

export function updateTag(id: number, tag: Partial<Tag>) {
    return consoleApi.patch<Tag>({
        endpoint: `/tag/${id}`,
        data: tag
    })
}

export function checkSlugAvailability(tagId: number, slug: string, signal: AbortSignal) {
    return consoleApi.get<{available: boolean}>({
        endpoint: `/tag/${tagId}/slug-available`,
        data: {
            slug
        },
        signal
    })
}