import { kea } from "kea";
import api from "../lib/api";

const languagesLogic = kea({

    key: props => props.subdomain,

    path: key => [key, 'languages'],

    actions: {
        setLanguages: (langs) => ({langs}),
        addLanguage: (lang) => ({lang}),
        updateLanguage: (lang) => ({lang}),
        removeLanguage: (id) => ({id}),
    },

    ajax: ({actions, props}) => ({

        load: async () => {
            const langs = await api.get(props.subdomain, '/languages');
            actions.setLanguages(langs);
        },

        create: async ({code, name, onCreate}) => {
            const lang = await api.post(props.subdomain, '/language', {code, name});
            actions.addLanguage(lang);
            onCreate();
        },

        update: async ({code, name, id, onUpdate}) => {
            const lang = await api.put(props.subdomain, `/language/${id}`, {code, name});
            actions.updateLanguage(lang);
            onUpdate()
        },

        remove: async ({id}) => {
            actions.removeLanguage(id);
            await api.delete(props.subdomain, `/language/${id}`);
        },

    }),

    reducers: ({props}) => ({

        languages: [[], {
            setLanguages: (_, {langs}) => langs,
            addLanguage: (state, {lang}) => [...state, lang],
            updateLanguage: (state, {lang}) => state.map(
                stateLang => stateLang.id === lang.id ? lang : stateLang
            ),
            removeLanguage: (state, {id}) => state.filter(lang => lang.id !== id)
        }],
    }),

    selectors: {

        getLanguageById: [
            s => [s.languages],
            languages => id => languages.find(l => l.id == id)
        ],

        primaryLanguage: [
            s => [s.languages],
            languages => languages.find(l => l.is_primary === true)
        ]

    },

});

export default languagesLogic;
