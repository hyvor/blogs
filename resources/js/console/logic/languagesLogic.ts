import {actions, kea, key, path, props, reducers, selectors} from "kea";
import { ajax } from "kea-ajax";
import api from "../lib/api";
import {languagesLogicType} from "./languagesLogicType";
import {Language} from "../types";

const languagesLogic = kea<languagesLogicType>([

    props({} as {subdomain: string}),

    key((props) => props.subdomain),

    path(key => ['languages', key]),

    actions({
        setLanguages: (langs: Language[]) => ({langs}),
        addLanguage: (lang: Language) => ({lang}),
        updateLanguage: (lang: Language) => ({lang}),
        removeLanguage: (id: number) => ({id}),
    }),

    ajax(({actions, props}) => ({

        load: async () => {
            const langs = await api.get<Language[]>(props.subdomain, '/languages');
            actions.setLanguages(langs);
        },

        create: async (
            {code, name, onCreate} :
            {code: string, name: string, onCreate: Function}
        ) => {
            const lang = await api.post<Language>(props.subdomain, '/language', {code, name});
            actions.addLanguage(lang);
            onCreate();
        },

        update: async (
            {id, code, name, onUpdate} :
            {id: number, code: string, name: string, onUpdate: Function}
        ) => {
            const lang = await api.put<Language>(props.subdomain, `/language/${id}`, {code, name});
            actions.updateLanguage(lang);
            onUpdate()
        },

        remove: async (
            {id} :
            {id: number}
        ) => {
            actions.removeLanguage(id);
            await api.delete(props.subdomain, `/language/${id}`);
        },

    })),

    reducers(({props}) => ({
        languages: [
            [] as Language[],
            {
                setLanguages: (_, {langs}) => langs,
                addLanguage: (state, {lang}) => [...state, lang],
                updateLanguage: (state, {lang}) => state.map(
                    stateLang => stateLang.id === lang.id ? lang : stateLang
                ),
                removeLanguage: (state, {id}) => state.filter(lang => lang.id !== id)
            }
        ],
    })),

    selectors({

        getLanguageById: [
            s => [s.languages],
            (languages) : (id: number) => Language | undefined => id => languages.find(l => l.id == id)
        ],

        primaryLanguage: [
            s => [s.languages],
            (languages) : Language => languages.find(l => l.is_primary) as Language
        ]

    }),

]);

export default languagesLogic;
