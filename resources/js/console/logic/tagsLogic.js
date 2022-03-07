import { kea } from "kea";
import api from "../lib/api";

const tagsLogic = kea({

    key: props => props.subdomain,

    path: key => ['tag', key],

    actions: {
        setTagList: (tag) => ({tag}),
        setTagsListHasMore: (has) => ({has}),
        removeFromList: (id) => ({id}),
        addTag: (tag) => ({tag}),
        updateTag: (tag) => ({tag}),
    },

    ajax: ({ values, actions, props }) => ({ 

        load: async ({offset = 0, type}) => {
            const tag =  await api.get(props.subdomain, '/tags');
            actions.setTagsListHasMore(tag.length === 50);
            actions.setTagList(tag);
        },

        loadTagsListMore: async ({offset}) => {
            const response = await api.get(props.subdomain, '/tags', {
                offset
            });
            actions.setTagsListHasMore(response.length === 50);
            actions.setTagList([...values.tag, ...response])
        },

        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/tag/${id}`);
        },
            
        create: async ({name, description, slug}) => {
            const tag = await api.post(props.subdomain, '/tag', {
                name: name,
                description: description,
                slug: slug,
            })
            actions.addTag(tag);
        },
            
        updateData: async ({tagId, name, description, slug }) => {
            const tag = await api.put(props.subdomain, `/tag/${tagId}`, {
                name: name,
                description: description,
                slug: slug,
            });
            actions.addTag(tag);
        },

    }),

    reducers: {

        tag: [[], {
            setTagList: (_, {tag}) => tag,
            removeFromList: (state, {id}) => state.filter(m => m.id !== id),
            addTag: (state, {tag}) => [tag, ...state],
            updateTag:(state, {tag}) => state.map(
                stateTag => stateTag.id === tag.id ? tag : stateTag
            ),
        }],
        tagListHasMore: [false, {
            setTagsListHasMore: (_, {has}) => has 
        }],
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default tagsLogic;
