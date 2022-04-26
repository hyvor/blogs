import { kea } from "kea";
import api from "../lib/api";

const routesLogic = kea({

    key: props => props.subdomain,

    path: key => ['route', key],

    actions: {
        setRouteList: (route) => ({route}),
        removeFromList: (id) => ({id}),
        addRoute: (route) => ({route}),
        updateRoute: (route) => ({route}),
    },

    ajax: ({ values, actions, props }) => ({ 

        load: async () => {
            const route = await api.get(props.subdomain, '/route');
            actions.setRouteList(route);
        },

        remove: async ({id}) => {
            // console.log(id)
            actions.removeFromList(id);
            await api.delete(props.subdomain, `/route/${id}`);
        },

        create: async ({name, match, template}) => {
            // console.log(name, match, template);
            const route = await api.post(props.subdomain, '/route', {
                    name: name,
                    match: match,
                    template:template
            })
            actions.addRoute(route); 
        },

        updateData: async ({id, name, match, template, postsFilter, contentType}) => {
            // console.log(id, name, match, postsFilter, contentType);
            const route = await api.put(props.subdomain, `/route/${id}`, {
                    name: name,
                    match: match,
                    template: template,
                    postsFilter: postsFilter,
                    contentType: contentType,
                });
            actions.addRoute(route);
        },
    }),

    reducers: {

        route: [[], {
            setRouteList: (_, {route}) => route,
            removeFromList: (state, {id}) => state.filter(m => m.id !== id),
            addRoute: (state, {route}) => [route, ...state],
            updateRoute:(state, {route}) => state.map(
                stateRoute => stateRoute.id === route.id ? route : stateRoute
            ),
        }],
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default routesLogic;