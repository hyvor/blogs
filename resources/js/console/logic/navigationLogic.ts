import {actions, events, kea, key, path, props, reducers, selectors} from "kea";
import api from "../lib/api";
import type { navigationLogicType } from "./navigationLogicType";
import {ajax} from "kea-ajax";
import {Navigation, NavigationType, NavigationVariant} from "../types";
import merge from "deepmerge";

const  navigationLogic = kea<navigationLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),

    path(key => ['navigation', key]),

    actions({

        setNavigations: (navigations: Navigation[]) => ({navigations}),
        addNavigation: (navigation: Navigation) => ({navigation}),
        updateNavigation: (navigation: Navigation) => ({navigation}),
        removeNavigation: (id: number) => ({id}),

        addNavigationVariant: (id: number, variant: NavigationVariant) => ({id, variant}),
        removeVariant: (variant: NavigationVariant) => ({variant}), // TODO
        updateNavigationVariant: (id: number, variant: NavigationVariant) => ({id, variant}),

        updateSortValue: (id: number, sort: number) => ({id, sort}),

    }),

    ajax(({actions, values, props}) => ({

        load: async () => {
            const navigations = await api.get<Navigation[]>(props.subdomain, '/navigations');
            actions.setNavigations(navigations);
        },

        remove: async ({id} : {id: number}) => {
            await api.delete(props.subdomain, `/navigation/${id}`);
            actions.removeNavigation(id)
        },

        create: async ({name, url, type, onCreate} : {name: string, url: string, type: NavigationType, onCreate: Function}) => {
            const navigation = await api.post<Navigation>(props.subdomain, '/navigation', {
                name: name,
                url: url,
                type: type
            })
            actions.addNavigation(navigation);

            onCreate();
        },

        update: async (
            {id, url, type, variants, onUpdate} :
            {id: number, url: string, type: NavigationType, variants: NavigationVariant[], onUpdate: Function}
        ) => {

            for (let newVariant of variants) {

                const variant =
                    (values.navigations.find(nav => nav.id === id) as Navigation)
                        .variants.find(v => v.language_id === newVariant.language_id)

                if (!variant)
                    continue;

                if (newVariant.name !== variant.name) {
                    await api.put<NavigationVariant>(props.subdomain, `/navigation/${id}/variant`, {
                        language_id: variant.language_id,
                        name: newVariant.name,
                    })
                }

            }

            const navigation = await api.put<Navigation>(props.subdomain, `/navigation/${id}`, {
                url,
                type
            })

            actions.updateNavigation(navigation)

            onUpdate();
        },

        createVariant: async ({id, languageId, onCreate} : {id: number, languageId: number, onCreate: Function}) => {

            const variant = await api.post<NavigationVariant>(props.subdomain, `/navigation/${id}/variant`, {
                language_id: languageId,
            })
            actions.addNavigationVariant(id, variant);

            onCreate(variant);

        },

        saveSort: async ({type} : {type: NavigationType}) => {
            const navigations = type === 'header' ? values.headerNavigations : values.footerNavigations

            const ids = navigations.sort((a, b) => (a.sort - b.sort)).map(nav => nav.id)
            await api.patch(props.subdomain, `/navigations/sort`, {ids})
        }

    })),

    reducers({

        navigations: [
            [] as Navigation[],
            {
                setNavigations: (_, {navigations}) => navigations,
                addNavigation: (state, {navigation}) => [...state, navigation],
                updateNavigation: (state, {navigation}) => state.map(
                    stateNav => stateNav.id === navigation.id ? navigation : stateNav
                ),
                removeNavigation: (state, {id}) => state.filter(nav => nav.id !== id),

                addNavigationVariant: (state, {id, variant}) => {
                    return state.map(nav => {
                        if (nav.id === id) {
                            const copy = {...nav}
                            copy.variants.push(variant)
                            return copy;
                        } else {
                            return nav;
                        }
                    });
                },

                /*updateNavigationVariant: (state, {id, variant}) => {

                },*/

                updateSortValue: (state, {id, sort}) => {
                    return state.map(nav => nav.id === id ? {...nav, sort} : nav)
                }
            }
        ],

    }),

    selectors({

        headerNavigations: [
            (s) => [s.navigations],
            (navigations) => navigations.filter(nav => nav.type === 'header').sort((a, b) => a.sort - b.sort)
        ],

        footerNavigations: [
            (s) => [s.navigations],
            (navigations) => navigations.filter(nav => nav.type === 'footer').sort((a, b) => a.sort - b.sort)
        ],

    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

]);

export default navigationLogic;