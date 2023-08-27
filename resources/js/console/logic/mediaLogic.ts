import {actions, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";
import type { mediaLogicType } from "./mediaLogicType";
import {ajax} from "kea-ajax";
import {Media, UnsplashImage} from "../types";
import { OnSelect } from "../ReusableComponents/ImageUploader/ImageUploader";
import getSubdomain from "../logic-helpers/subdomain";

export interface GlobalImageUploaderConfig {
    onSelect: OnSelect,
    onClose?: () => void,
}

export function setGlobalImageUploader(config: GlobalImageUploaderConfig | null) {
    const logic = mediaLogic({subdomain: getSubdomain()});
    logic.mount();
    logic.actions.setGlobalImageUploader(config);
}

const mediaLogic = kea<mediaLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),

    path(key => ['media', key]),

    actions({
        setMediaList: (media: Media[]) => ({media}),
        removeFromList: (id: number) => ({id}),
        addMedia: (media: Media) => ({media}),

        setGlobalImageUploader: (config: GlobalImageUploaderConfig | null) => ({config}),
    }),

    ajax(({actions, props}) => ({

        /**
         * Inside media settings
         * ==================
         */
        load: async ({offset, type} : {offset?: number, type?: string}) => {
            const media = await api.get<Media[]>(props.subdomain, '/media', {
                offset,
                limit: 50,
                type,
            });
            actions.setMediaList(media);
        },

        loadImages: async(
            {limit = 50, offset = 0, search = null, onLoad} : 
            {
                limit?: number,
                offset?: number, 
                search?: string | null,
                onLoad: (media: Media[]) => void
            }
        ) => {
            const media = await api.get<Media[]>(props.subdomain, '/media', {
                offset,
                limit,
                type: 'image',
                search
            });
            onLoad(media);
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
        uploadImage: async (
            {file, onUpload, onError} : 
            {file: File, onUpload: (media: Media) => void, onError: Function}
        ) => {
            var formData = new FormData();
            formData.append('file', file, file.name);
            try {
                const media = await api.post<Media>(props.subdomain, '/media', formData);
                onUpload(media);
            } catch (e) {
                onError(e);
            }
        },

        uploadImageFromUrl: async ({url, postId = null, onUpload}) => {

            const media = await api.post<Media>(props.subdomain, '/media/from-url', {
                url,
                post_id: postId
            });

            onUpload(media);
        },

        searchUnsplash: async (
            {query, page = 1, onLoad} : 
            {query: string, page?:number, onLoad: (results: UnsplashImage[]) => void}
        ) => {

            const results = await api.get<UnsplashImage[]>(props.subdomain, '/media/unsplash/search', {
                search: query,
                page
            });

            onLoad(results);

        },

    })),

    reducers({

        media: [
            [],
            {
                setMediaList: (_, {media}) => media,
                removeFromList: (state, {id}) => state.filter(m => m.id !== id),
                addMedia: (state, {media}) => [media, ...state]
            }
        ],

        globalImageUploader: [
            null as null | GlobalImageUploaderConfig,
            {
                setGlobalImageUploader: (_, {config}) => config
            }
        ]

    })

])

export default mediaLogic;