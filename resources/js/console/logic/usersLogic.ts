import { kea, key, path, actions, reducers } from 'kea';
import {User} from "../types";

import type { usersLogicType } from "./usersLogicType";

export interface IDKeyedUsers {
    [key: number]: User
}

const usersLogic = kea<usersLogicType>([
    key((props: {subdomain: string}) => props.subdomain),
    path((key: string) => [key, 'users']),
    actions({
        addUsers: (users: Array<User>) => ({users})
    }),
    reducers({

        users: [
            {},
            {
                addUsers: (state: IDKeyedUsers, { users } : { users: Array<User> }) => {
                    const usersKeyed : IDKeyedUsers = {};
                    for (let user of users) {
                        usersKeyed[user.id] = user;
                    }

                    return {...state, ...usersKeyed}
                }
            }
        ],

    })
])

export default usersLogic

// import { kea } from "kea";
// import api from "../lib/api";
//
// const usersLogic = kea({
//
//     key: props => props.subdomain,
//
//     path: key => ['user', key],
//
//     actions: {
//         setUsersList: (user) => ({user}),
//         setUsersListHasMore: (has) => ({has}),
//         removeFromList: (id) => ({id}),
//         addUser: (user) => ({user}),
//         addUserVarian: (user) => ({user}),
//         updateUser: (user) => ({user}),
//
//         // setPicture: (user) => ({user}),
//         addPicture: (user) => ({user}),
//     },
//
//     ajax: ({ values, actions, props }) => ({
//
//         load: async ({offset = 0, type}) => {
//             const user =  await api.get(props.subdomain, '/users');
//             actions.setUsersListHasMore(user.length === 50);
//             actions.setUsersList(user);
//         },
//
//         loadUsersListMore: async ({offset}) => {
//             const response = await api.get(props.subdomain, '/users', {
//                 offset
//             });
//             actions.setUsersListHasMore(response.length === 50);
//             actions.setUsersList([...values.user, ...response])
//         },
//
//         remove: async ({id, languageId}) => {
//             console.log(id, languageId)
//             actions.removeFromList(id);
//             await api.delete(props.subdomain, `/user/${id}`,{
//                 languageId: languageId,
//             });
//         },
//
//         create: async ({name, email, slug, role, status}) => {
//             // console.log(name, email,slug, role, status)
//             const user = await api.post(props.subdomain, '/user', {
//                 role:role,
//                 status:status,
//                 slug:slug,
//                 email:email,
//                 name: name,
//             })
//             actions.addUser(user);
//         },
//
//         createVariant: async ({userId, languageId}) => {
//             // console.log(userId, languageId)
//             const user = await api.post(props.subdomain, '/user/variant', {
//                 userId: userId,
//                 languageId: languageId,
//             })
//             actions.addUserVarian(user);
//         },
//
//         updateData: async ({
//             id, name, email, slug, role, status, url, social_facebook, social_twitter,
//             social_linkedin, social_youtube, social_instagram, bio, location
//             }) => {
//
//             console.log(id, name, email, slug, role, status, url, social_facebook, social_twitter,
//             social_linkedin, social_youtube, social_instagram, bio, location )
//
//             const user = await api.patch(props.subdomain, `/user/${id}`, {
//                 role:role,
//                 status:status,
//                 slug:slug,
//                 email:email,
//                 name: name,
//                 url:url,
//                 social_facebook:social_facebook,
//                 social_twitter:social_twitter,
//                 social_linkedin:social_linkedin,
//                 social_youtube: social_youtube,
//                 social_instagram:social_instagram,
//                 bio:bio,
//                 location:location,
//             });
//             actions.updateUser(user);
//         },
//
//         // loadPicture: async () => {
//         //     const user = await api.get(props.subdomain, '/user/picture' , {
//         //         userId:1,
//         //     });
//         //     actions.setPicture(user);
//         // },
//
//         upload: async ({file}) => {
//             var formData = new FormData();
//             formData.append('file', file, file.name);
//             const user = await api.post(props.subdomain, '/user/picture', formData);
//             actions.addPicture(user);
//         },
//
//     }),
//
//     reducers: {
//
//         user: [[], {
//             setUsersList: (_, {user}) => user,
//             removeFromList: (state, {id}) => state.filter(m => m.id !== id),
//             addUser: (state, {user}) => [user, ...state],
//             // addUserVarian: (state, {user}) => [user, ...state],
//             updateUser:(state, {user}) => state.map(
//                 stateUser => stateUser.id === user.id ? user : stateUser
//             ),
//         }],
//         userListHasMore: [false, {
//             setUsersListHasMore: (_, {has}) => has
//         }],
//         createNewVarian: [[], {
//             addUserVarian: (state, {user}) => [user, ...state],
//         }],
//         picture: [[], {
//             // setPicture: (_, {user}) => user,
//             addPicture: (state, {user}) => [user, ...state]
//         }],
//     },
//
//     events: ({actions}) => ({
//         afterMount: actions.load
//     })
//
// });
//
// export default usersLogic;
