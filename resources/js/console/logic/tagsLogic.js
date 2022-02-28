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
        savePostTag: (tag) => ({tag}),
        getPostTag: (tag) => ({tag}),
    },

    ajax: ({ values, actions, props }) => ({ 

        load: async ({offset = 0, type}) => {
            // const tag = await api.get(props.subdomain, '/tags', {
            //     offset,
            //     limit: 20,
            //     type,
            // });
            const tag = await api.get(props.subdomain, '/tags');
            actions.setTagsListHasMore(tag.length === 50);
            // actions.setTagList(tag.map(val => val.id))
            actions.setTagList(tag);
        },

        // load: async () => {
        //     const tag = await api.get(props.subdomain, '/tags');
        //     actions.setTagList(tag);
        // },

        loadTagsListMore: async ({offset}) => {
            // console.log(offset)
            const response = await api.get(props.subdomain, '/tags', {
                offset
            });
            actions.setTagsListHasMore(response.length === 50);
            actions.setTagList([...values.tag, ...response])
            // actions.setTagList(response.map(val => val.id))
            // actions.setTagList(tag)
        },

        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/tag/${id}`);
        },
            
        create: async ({name, description, slug, featuredImage}) => {
            console.log(name, description, slug)
            const tag = await api.post(props.subdomain, '/tag', {
                name: name,
                description: description,
                slug: slug,
                // featuredImage: featuredImage 
            })
            actions.addTag(tag);
        },
            
        updateData: async ({tagId, name, description, slug, featuredImage}) => {
            console.log(tagId, name, description, slug)

            const tag = await api.put(props.subdomain, `/tag/${tagId}`, {
                name: name,
                description: description,
                slug: slug,
                featuredImage: featuredImage 
            });
            actions.addTag(tag);
        },

        saveId: async ({postId, tagId}) => {
            console.log('tag id '+ tagId + ' post id ' + postId);
            const tag = await api.post(props.subdomain, '/create-post-tag', {
                postId: postId,
                tagId: tagId,
            })
            actions.savePostTag(tag);
        },

        getSelectedTags: async () => {
            const tag = await api.get(props.subdomain, '/get-post-tag');
            actions.getPostTag(tag);
        },
    }),

    reducers: {

        tag: [[], {
            setTagList: (_, {tag}) => tag,
            // setTagsListHasMore: (_, {has}) => has,

            removeFromList: (state, {id}) => state.filter(m => m.id !== id),
            addTag: (state, {tag}) => [tag, ...state],
            updateTag:(state, {tag}) => state.map(
                stateTag => stateTag.id === tag.id ? tag : stateTag
            ),
            savePostTag: (state, {tag}) => [tag, ...state],
            getPostTag: (_, {tag}) => tag,
        }],

        tagList: [[], {
            setTagList: (_, {tag}) => tag
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
