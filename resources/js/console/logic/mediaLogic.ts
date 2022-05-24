import {actions, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";
import type { mediaLogicType } from "./mediaLogicType";
import {ajax} from "kea-ajax";
import {Media} from "../types";

const mediaLogic = kea<mediaLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),

    path(key => ['media', key]),

    actions({
        setMediaList: (media: Media[]) => ({media}),
        removeFromList: (id: number) => ({id}),
        addMedia: (media: Media) => ({media}),
    }),

    ajax(({actions, props}) => ({

        /**
         * Inside media settings
         * ==================
         */
        load: async ({offset, type} : {offset: number, type: string}) => {
            const media = await api.get<Media[]>(props.subdomain, '/media', {
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
            const media = await api.post<Media>(props.subdomain, '/media', formData);
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

    })),

    reducers({

        media: [
            [],
            {
                setMediaList: (_, {media}) => media,
                removeFromList: (state, {id}) => state.filter(m => m.id !== id),
                addMedia: (state, {media}) => [media, ...state]
            }
        ]

    })

])

export default mediaLogic;