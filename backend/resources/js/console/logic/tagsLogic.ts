import {actions, kea, key, path, props, reducers} from "kea";
import {Tag, TagVariant} from "../types";

import type { tagsLogicType } from "./tagsLogicType";
import {ajax} from "kea-ajax";
import api from "../lib/api";
import {diff as getDiff} from "deep-object-diff";

export interface IDKeyedTags {
    [key: number]: Tag
}

const tagsLogic = kea<tagsLogicType>([

    props({} as {subdomain: string}),
    key((props) => props.subdomain),
    path((key) => ['tags', key]),

    actions({
        addTags: (tags: Array<Tag>) => ({tags}),
        setTagsList: (tagIds: number[]) => ({tagIds}),

        removeTag: (id: number) => ({id}),

        addTagVariant: (id: number, variant: TagVariant) => ({id, variant}),

        setTagsListHasMore: (hasMore: boolean) => ({hasMore})
    }),

    ajax(({props, actions, values}) => ({

        load: async () => {
            const tags = await api.get<Tag[]>(props.subdomain, '/tags')
            actions.addTags(tags)
            actions.setTagsList(tags.map(tag => tag.id))
            actions.setTagsListHasMore(tags.length === 50);
        },

        loadMore: async () => {
            const tags = await api.get<Tag[]>(props.subdomain, '/tags', {
                offset: values.tagsList.length
            })
            actions.addTags(tags)
            actions.setTagsList([...values.tagsList, ...tags.map(tag => tag.id)])
            actions.setTagsListHasMore(tags.length === 50);
        },

        create: async ({name, onCreate} : {name: string, onCreate: (tag: Tag) => void}) => {
            const tag = await api.post<Tag>(props.subdomain, '/tag', {name})
            actions.addTags([tag])
            actions.setTagsList([tag.id, ...values.tagsList])
            onCreate(tag);
        },

        update: async ({tag, onUpdate} : {tag: Tag, onUpdate: Function}) => {
            const tagOriginal = values.tags[tag.id]

            const diff = getDiff(tagOriginal, tag) as Partial<Tag>

            if (diff.variants) {

                for (const variant of tag.variants) {

                    const variantOriginal = tagOriginal.variants.find(t => t.language_id === variant.language_id)

                    if (!variantOriginal)
                        continue;

                    const variantDiff = getDiff(variantOriginal, variant)

                    await api.patch(props.subdomain, `/tag/${tag.id}/variant`, {
                        ...variantDiff,
                        language_id: variant.language_id
                    })

                }

                delete diff.variants;

            }

            const newTag = await api.patch<Tag>(props.subdomain, `/tag/${tag.id}`, diff)

            actions.addTags([newTag])

            onUpdate();
        },

        createVariant: async ({ id, languageId, onCreate }) => {
            const variant = await api.post<TagVariant>(props.subdomain, `/tag/${id}/variant`, {
                language_id: languageId
            })
            actions.addTagVariant(id, variant)
            onCreate(variant)
        },

        remove: async ({id} : {id: number}) => {
            actions.removeTag(id);
            await api.delete(props.subdomain, `/tag/${id}`);
        },

    })),

    reducers({

        tagsList: [
            [] as number[],
            {
                setTagsList: (_, {tagIds}) => tagIds,
                removeTag: (state, {id}) => state.filter(stateId => stateId !== id)
            }
        ],

        tagsListHasMore: [
            false,
            {
                setTagsListHasMore: (_, {hasMore}) => hasMore
            }
        ],

        tags: [
            {} as IDKeyedTags,
            {
                addTags: (state, { tags } : { tags: Array<Tag> }) => {
                    const tagsKeyed : IDKeyedTags = {};
                    for (let tag of tags) {
                        tagsKeyed[tag.id] = tag;
                    }

                    return {...state, ...tagsKeyed}
                },

                addTagVariant: (state, {id, variant}) => {
                    const copy = {...state}
                    copy[id].variants.push(variant)
                    return copy;
                },

            }
        ]

    })

])

export default tagsLogic
