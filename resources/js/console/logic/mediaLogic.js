import { kea } from "kea";
import api from "../lib/api";


const mediaLogic = kea({

    key: props => props.subdomain,

    path: key => ['media', key],

    actions: {
        setMediaList: (media) => ({media}),
        removeFromList: (id) => ({id}),
        addMedia: (media) => ({media}),
    },

    ajax: ({actions, props}) => ({

        /**
         * Inside media settings
         * ==================
         */
        load: async ({offset = 0, type}) => {
            const media = await api.get(props.subdomain, '/media', {
                offset,
                limit: 50,
                type,
            });
            actions.setMediaList(media);
        },
        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/media/${id}`);
        },
        upload: async ({file}) => {
            var formData = new FormData();
            formData.append('file', file, file.name); 
            const media = await api.post(props.subdomain, '/media', formData);
            actions.addMedia(media);
        },

        /**
         * Outside media settings (common)
         * ===============
         */
        uploadImage: async ({file, onUpload}) => {
            var formData = new FormData();
            formData.append('file', file, file.name);
            const media = await api.post(props.subdomain, '/media', formData);
            onUpload(media);
        }

    }),

    reducers: {

        media: [[], {
            setMediaList: (_, {media}) => media,
            removeFromList: (state, {id}) => state.filter(m => m.id !== id),
            addMedia: (state, {media}) => [media, ...state]
        }]

    },

});

export default mediaLogic;