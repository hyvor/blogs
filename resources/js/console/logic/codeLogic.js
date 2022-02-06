import { kea } from "kea";
import api from "../lib/api";

const codeLogic = kea({

    key: props => props.subdomain,

    path: key => ['code', key],

    actions: {
        getCode: (code) => ({code}),
        updateCode: (code) => ({code}),
    },

    ajax: ({actions, props}) => ({

        load: async ({offset = 0}) => {
            const code = await api.get(props.subdomain, '/code', {
                offset,
                limit: 10,
            });
            actions.getCode(code);
        },

        updateData: async ({ codeHead, codeFooter }) => {
            const code = await api.put(props.subdomain, '/code', {
                    codeHead: codeHead,
                    codeFooter: codeFooter,
                });
            actions.updateCode(code);
        },
    }),

    reducers: {

        code: [[], {
            getCode: (_, {code}) => code,
            updateCode: (state, {code}) => [code, ...state],
        }]
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default codeLogic;