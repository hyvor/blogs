import { kea } from "kea";
import api from "../../lib/api";

const postUsersLogic = kea({

    key: props => props.subdomain,

    path: key => ['pu', key],

    actions: {
        setPostUserList: (pu) => ({pu}),
        addPostUser: (pu) => ({pu}),
        setPostUser: (pu) => ({pu}),
    },

    ajax: ({ actions, props }) => ({ 

        // Post tag section starts from here.
        load: async ({postId}) => {
            const pu = await api.get(props.subdomain, '/getTagList', {
                postId: postId,
            });
            // console.log(pu);
            actions.setPostUserList(pu);
        },

        createId: async ({postId, tagId}) => {
            console.log('tag id '+ tagId + ' post id ' + postId);
            const pu = await api.post(props.subdomain, '/createPostTag', {
                postId: postId,
                tagId: tagId,
            })
            actions.addPostUser(pu);
        },

        loadSelectedTags: async ({postId}) => {
            const pu = await api.get(props.subdomain, '/getPostTag', {
                postId: postId,
            });
            // console.log('load the selected tags')
            // console.log(pu);
            actions.setPostUser(pu);
        },
    }),

    reducers: {

        // Post->Tag section start.
        pu: [[], {
            setPostUserList: (_, {pu}) => pu,
            addPostUser: (state, {postTag}) => [postTag, ...state],
        }],

        selectedPU: [[], {
            setPostUser: (_, {pu}) => pu,
        }],
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default postUsersLogic;
