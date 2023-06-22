import {actions, events, kea, props, reducers} from "kea";
import {ajax} from "kea-ajax";
import type { hyvorTalkLogicType } from "./hyvorTalkLogicType";
import api from "../../lib/api";

interface HyvorTalkIntegration {
    id: number,
    created_at: number,
    website_id: number,
}

type HyvorTalkIntegrationData = {
    connected: false
} | {
    connected: true,
    data: HyvorTalkIntegration
}

const hyvorTalkLogic = kea<hyvorTalkLogicType>([

    props({} as {subdomain: string}),

    actions({
        setData: (data: HyvorTalkIntegrationData) => ({data})
    }),

    ajax(({props, actions}) => ({

        load: async () => {
            const data = await api.get<HyvorTalkIntegrationData>(props.subdomain, '/integrations/hyvor-talk');
            actions.setData(data);
        },


        createIntegration: async ({onSuccess} : {onSuccess: Function}) => {
            const data = await api.post<HyvorTalkIntegrationData>(props.subdomain, '/integrations/hyvor-talk');
            actions.setData({
                connected: true,
                data
            });
            onSuccess();
        }

    })),

    reducers({
        data: [
            {} as HyvorTalkIntegrationData,
            {
                setData: (state, {data}) => data
            }
        ]
    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

]);

export default hyvorTalkLogic;