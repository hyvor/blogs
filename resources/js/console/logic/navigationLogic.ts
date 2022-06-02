import {actions, events, kea, key, path, props, reducers} from "kea";
import api from "../lib/api";
import {navigationLogicType} from "./navigationLogicType";
import {ajax} from "kea-ajax";
import {Navigation, NavigationType, NavigationVariant} from "../types";

const  navigationLogic = kea<navigationLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),

    path(key => ['navigation', key]),

    actions({

        setNavigations: (navigations: Navigation[]) => ({navigations}),
        addNavigation: (navigation: Navigation) => ({navigation}),
        updateNavigation: (navigation: Navigation) => ({navigation}),

        addVariant: (variant: NavigationVariant) => ({variant}),
        removeVariant: (id) => ({}),


        /*getNavigationList: (navigation) => ({navigation}),
        removeFromList: (id) => ({id}),
        addNavigation: (navigation) => ({navigation}),
        updateNavigation: (navigation) => ({navigation}),
        updateDestination: (navigation) => ({navigation}),
        updateSource: (navigation) => ({navigation}),
        addNavigationVarian: (navigation) => ({navigation}),*/

    }),

    ajax(({actions, props}) => ({

        load: async ({offset = 0, type} : {offset: number, type: NavigationType}) => {
            const navigation = await api.get<Navigation[]>(props.subdomain, '/navigations', {
                offset,
                limit: 10,
                type,
            });
            actions.getNavigationList(navigation);
        },

        remove: async ({id, languageId}) => {
            console.log(id, languageId);
            // actions.removeFromList(id);
            // await api.delete(props.subdomain, `/navigation/${id}`, {
            //     languageId: languageId,
            // });
        },

        create: async ({name, url, type}) => {
            console.log(name, url,type)
            const navigation = await api.post(props.subdomain, '/navigation', {
                    name: name,
                    url: url,
                    type: type 
                })
            actions.addNavigation(navigation);
        },

        createVariant: async ({navigationId, languageId, name}) => {
            // console.log(navigationId, languageId, name)
            const navigation = await api.post(props.subdomain, '/navigation/variant', {
                id: navigationId,
                languageId: languageId,
                name: name,
            })
            actions.addNavigationVarian(navigation);
        },

        updateData: async ({userId, languageId, name, url, type}) => {
            console.log(userId, languageId, name, url, type)
            const navigation = await api.put(props.subdomain, `/navigation/${userId}`, {
                    languageId: languageId,
                    name: name,
                    url: url,
                    type: type 
                });
            actions.updateNavigation(navigation);
        },

        updateItemNumber: async ({NavigationId, destinationId}) => {
            console.log('Navigation' + NavigationId + ' I think its working')
            console.log('Lets see '+ destinationId + ' destination ID')

            const navigation = await api.put(props.subdomain, `/navigation/sort/${NavigationId}`, {
                navigationSort: destinationId,
            });
            actions.updateDestination(navigation);
        },

        updateSourceNav: async ({destinationId, sourceId}) => {
            console.log(destinationId)
            console.log(sourceId + ' source ID')

            const navigation = await api.put(props.subdomain, `/navigation/source/${destinationId}`, {
                    sort: sourceId,
                });
            actions.updateSource(navigation);
        },
    })),

    reducers({

        navigations: [
            [] as Navigation[],
            {
                setNavigations: (_, {navigations}) => navigations,
                addNavigation: (_, {navigation}) => navigation,


                removeFromList: (state, {id}) => state.filter(m => m.id !== id),
                addNavigation: (state, {navigation}) => [navigation, ...state],
                updateNavigation: (state, {navigation}) => [navigation, ...state],
                updateDestination: (state, {navigation}) => [navigation, ...state],
                updateSource: (state, {navigation}) => [navigation, ...state],
            }
        ],

    }),

    events(({actions}) => ({
        afterMount: actions.load
    }))

]);

export default navigationLogic;