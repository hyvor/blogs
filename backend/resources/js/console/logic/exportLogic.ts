import {actions, events, kea, key, path, props, reducers} from "kea";
import type { exportLogicType } from "./exportLogicType";
import {ajax} from "kea-ajax";
import api from "../lib/api";
import {Export} from "../types";

const exportLogic = kea<exportLogicType>([

    props({} as {subdomain: string}),
    key((props) => props.subdomain),
    path((key) => ['export', key]),

    actions(({values}) => ({
        setExports: (exports: Export[]) => ({exports}),
    })),

    ajax(({actions, values, props}) => ({

        getExports: async () => {
            const data : Export[] = await api.get(props.subdomain, '/data/exports');
            actions.setExports(data);
        },

        exportNow: async ({onLoad, onError} : {onLoad: Function, onError: Function}) => {

            let data: Export;

            try {
                data = await api.post(props.subdomain, '/data/export');
            } catch (e) {
                onError(e);
                return;
            }
            actions.setExports([data, ...values.exports]);
            onLoad();
        },

    })),

    reducers({
        exports: [
            [] as Export[],
            {
                setExports: (_, {exports}) => exports,
            }
        ]
    }),

    events(({actions}) => ({
        afterMount: () => {
            actions.getExports();
        }
    })),

]);

export default exportLogic;