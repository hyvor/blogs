import {actions, events, kea, key, path, props, reducers, selectors} from "kea";
import api from "../lib/api";
import {navigationLogicType} from "./navigationLogicType";
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

        addNavigationVariant: (variant: NavigationVariant) => ({variant}),
        removeVariant: (variant: NavigationVariant) => ({variant}),
        updateNavigationVariant: (id: number, variant: NavigationVariant) => ({id, variant}),

    }),

    ajax(({actions, props}) => ({

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

        update: async ({id, url} : {id:number, url: string}) => {
            const navigation = await api.patch<Navigation>(props.subdomain, `/navigation/${id}`, {
                url
            })
            actions.updateNavigation(navigation)
        },

        createVariant: async ({id, languageId, onCreate} : {id: number, languageId: number, onCreate: Function}) => {

            const variant = await api.post<NavigationVariant>(props.subdomain, `/navigation/${id}/variant`, {
                language_id: languageId,
            })
            actions.addNavigationVariant(variant);

            onCreate();

        },

        updateVariant: async (
            {id, languageId, name} :
            {id: number, languageId: number, name: string}
        ) => {

            const variant = await api.patch<NavigationVariant>(props.subdomain, `/navigation/${id}/variant`, {
                language_id: languageId,
                name
            })

            actions.updateNavigationVariant(id, variant)

        },

       /* updateData: async (
            {id, languageId, name, url, type} :
            { id: number, languageId: }
        ) => {

            const variant = await api.put<NavigationVariant>(props.subdomain, `/navigation/${id}`, {
                    languageId: languageId,
                    name: name,
                    url: url,
                    type: type
                });
            actions.updateNavigation(navigation);

        },*/

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

                updateNavigationVariant: (state, {id, variant}) => {
                    return state.map(nav => nav.id === id ?
                        merge(nav, {
                            variants: {
                                [variant.language_id]: variant
                            }
                        }) : nav
                    ) as Navigation[];
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