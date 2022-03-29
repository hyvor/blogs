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

        setVariantList: (tagVariant) => ({tagVariant}),
        removeVariantList: (id) => ({id}),
        addTagVarian: (tagVariant) => ({tagVariant}),
        updateTagVarian: (tagVariant) => ({tagVariant}),


        // addTagVarian: (tag) => ({tag}),
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
            const tag = await api.post(props.subdomain, '/tags', {
                name: name,
                description: description,
                slug: slug,
            })
            actions.addTag(tag);
        },
            
        updateData: async ({tagId, name, description, slug, codeHead, codeFoot}) => {
            const tag = await api.put(props.subdomain, `/tag/${tagId}`, {
                name: name,
                description: description,
                slug: slug,
                codeHead: codeHead,
                codeFoot: codeFoot,
            });
            actions.updateTag(tag);
        },


        // createVariant: async ({tagId, languageId}) => {
        //     console.log('kdkdkd')
        //     const tag = await api.post(props.subdomain, '/tagVariant', {
        //         tagId: tagId,
        //         languageId: languageId,
        //     })
        //     actions.addTagVarian(tag);
        // },

        // tag variant sections
        loadVariant: async ({tagId, languageId}) => {
            // console.log(tagId)
            // console.log(languageId)
            const tagVariant = await api.get(props.subdomain, '/tagVariant', {
                tagId: tagId,
                languageId: languageId,
            })
            console.log(tagVariant)
            // actions.setVariantList(tagVariant);
        },

        removeVariant: async ({tagId, languageId}) => {
            const tagVariant = await api.delete(props.subdomain, '/tagVariant', {
                tagId: tagId,
                languageId: languageId,
            })
            actions.removeVariantList(tagVariant);
        },
            
        createVariant: async ({tagId, languageId}) => {
            console.log(tagId)
            console.log(languageId)
            // const tagVariant = await api.post(props.subdomain, '/tagVariant', {
            //     tagId: tagId,
            //     languageId: languageId,
            // })
            // actions.addTagVarian(tagVariant);
        },
            
        updateDataVariant: async ({tagId, languageId, name, description}) => {
            const tagVariant = await api.put(props.subdomain, '/tagVariant', {
                tagId: tagId,
                languageId: languageId,
                name: name,
                description: description,
            });
            actions.updateTagVarian(tagVariant);
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

            // addTagVarian: (state, {tag}) => [tag, ...state],
        }],
        tagListHasMore: [false, {
            setTagsListHasMore: (_, {has}) => has 
        }],
        tagVariant: [[], {
            setVariantList: (_, {tagVariant}) => tagVariant,
            removeVariantList: (state, {id}) => state.filter(m => m.id !== id),
            addTagVarian: (state, {tagVariant}) => [tagVariant, ...state],
            
            updateTagVarian:(state, {tagVariant}) => state.map(
                stateTagVariant => stateTagVariant.id === tag.id ? tagVariant : stateTagVariant
            ),
        }],
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default tagsLogic;
