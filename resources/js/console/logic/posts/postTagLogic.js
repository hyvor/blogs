import { kea } from "kea";
import api from "../../lib/api";

const postTagLogic = kea({

    key: props => props.subdomain,

    path: key => ['pt', key],

    actions: {
        setPostTagList: (pt) => ({pt}),
        addPostTag: (pt) => ({pt}),
        // setPostTag: (pt) => ({pt}),
    },

    ajax: ({ actions, props }) => ({ 

        load: async ({postId}) => {
            return;
            const pt = await api.get(props.subdomain, `/postTags/${postId}`);
            actions.setPostTagList(pt);
        },

        // Post tag section starts from here.
        // load: async ({postId}) => {
        //     const pt = await api.get(props.subdomain, '/getTagList', {
        //         postId: postId,
        //     });
        //     actions.setPostTagList(pt);
        // },
 
        createId: async ({postId, tagId}) => {
            console.log('tag id '+ tagId + ' post id ' + postId);
            const pt = await api.post(props.subdomain, '/createPostTag', {
                postId: postId,
                tagId: tagId,
            })
            actions.addPostTag(pt);
        }, 

        // loadSelectedTags: async ({postId}) => {
        //     const pt = await api.get(props.subdomain, '/getPostTag', {
        //         postId: postId,
        //     });
        //     actions.setPostTag(pt);
        // },
    }),

    reducers: {

        // Post->Tag section start.
        pt: [[], {
            setPostTagList: (_, {pt}) => pt,
            addPostTag: (state, {postTag}) => [postTag, ...state],
        }],

        // selectedPT: [[], {
        //     setPostTag: (_, {pt}) => pt,
        // }],
    },

    events: ({actions}) => ({
        afterMount: actions.load
    })

});

export default postTagLogic;
