import { kea } from "kea";
import api from "../../lib/api";

const postTagLogic = kea({

    key: props => props.subdomain,

    path: key => ['pt', key],

    actions: {
        setPostTagList: (pt) => ({pt}),
        addPostTag: (pt) => ({pt}),
        setPostTag: (pt) => ({pt}),
    },

    ajax: ({ actions, props }) => ({ 

        // Post tag section starts from here.
        load: async ({postId}) => {
            const pt = await api.get(props.subdomain, '/getTagList', {
                postId: postId,
            });
            // console.log(pt);
            actions.setPostTagList(pt);
        },

        createId: async ({postId, tagId}) => {
            console.log('tag id '+ tagId + ' post id ' + postId);
            const pt = await api.post(props.subdomain, '/createPostTag', {
                postId: postId,
                tagId: tagId,
            })
            actions.addPostTag(pt);
        },

        loadSelectedTags: async ({postId}) => {
            const pt = await api.get(props.subdomain, '/getPostTag', {
                postId: postId,
            });
            // console.log('load the selected tags')
            // console.log(pt);
            actions.setPostTag(pt);
        },
    }),

    reducers: {

        // Post->Tag section start.
        pt: [[], {
            setPostTagList: (_, {pt}) => pt,
            addPostTag: (state, {postTag}) => [postTag, ...state],
        }],

        selectedPT: [[], {
            setPostTag: (_, {pt}) => pt,
        }],
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default postTagLogic;
