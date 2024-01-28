import {actions, events, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";

import type { apiKeysLogicType } from "./apiKeysLogicType";
import {ajax} from "kea-ajax";
import {ApiKey, ApiKeyType} from "../types";

const apiKeysLogic = kea<apiKeysLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),

    path(key => ['api-keys', key]),

    actions({
        setApiKeyList: (apiKeys: ApiKey[]) => ({apiKeys}),
        addApiKey: (apiKey: ApiKey) => ({apiKey}),
        removeFromList: (id: number) => ({id}),
    }),

    ajax(({ actions, props }) => ({

        load: async () => {
            const apiKey = await api.get<ApiKey[]>(props.subdomain, '/api-keys');
            actions.setApiKeyList(apiKey);
        },

        create: async ({ name, type, onCreate } : { name: string, type: ApiKeyType, onCreate: Function }) => {
            const apiKey = await api.post<ApiKey>(props.subdomain, '/api-key', {name, type})
            actions.addApiKey(apiKey);
            onCreate()
        },

        remove: async ({id}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/api-key/${id}`);
        },

    })),

    reducers({

        apiKeys: [
            [] as ApiKey[],
            {
                setApiKeyList: (_, {apiKeys}) => apiKeys,
                removeFromList: (state, {id}) => state.filter(m => m.id !== id),
                addApiKey: (state, {apiKey}) => [apiKey, ...state],
            }
        ],

    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

]);

export default apiKeysLogic;