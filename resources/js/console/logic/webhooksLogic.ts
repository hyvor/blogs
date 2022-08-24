import {actions, events, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";

import type { webhooksLogicType } from "./webhooksLogicType";
import {ajax} from "kea-ajax";
import {Webhook, WebhookEvent} from "../types";

const webhooksLogic = kea<webhooksLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),

    path(key => ['webhooks', key]),

    actions({
        setWebhookList: (webhooks: Webhook[]) => ({webhooks}),
        addWebhook: (webhook: Webhook) => ({webhook}),
        updateWebhook: (webhook: Webhook) => ({webhook}),
        removeFromList: (id: number) => ({id}),
    }),

    ajax(({ actions, props }) => ({

        load: async () => {
            const webhook = await api.get<Webhook[]>(props.subdomain, '/webhooks');
            actions.setWebhookList(webhook);
        },

        create: async ({ url, events, onCreate } : { url: string, events: WebhookEvent[], onCreate: Function }) => {
            const webhook = await api.post<Webhook>(props.subdomain, '/webhook', {url, events})
            actions.addWebhook(webhook);
            onCreate();
        },

        update: async ({ id, url, events, onUpdate } : { id: number, url: string, events: WebhookEvent[], onUpdate: Function }) => {
            const webhook = await api.patch<Webhook>(
                props.subdomain,
                '/webhook/' + id, {url, events}
            )
            actions.updateWebhook(webhook);
            onUpdate();
        },

        remove: async ({id} : {id: number}) => {
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/webhook/${id}`);
        },

    })),

    reducers({

        webhooks: [
            [] as Webhook[],
            {
                setWebhookList: (_, {webhooks}) => webhooks,
                removeFromList: (state, {id}) => state.filter(m => m.id !== id),
                addWebhook: (state, {webhook}) => [webhook, ...state],
                updateWebhook:(state, {webhook}) => state.map(
                    stateWebhook => stateWebhook.id === webhook.id ? webhook : stateWebhook
                ),
            }
        ],

    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

]);

export default webhooksLogic;