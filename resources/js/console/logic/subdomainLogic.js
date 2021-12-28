import { kea } from "kea";


export default kea({

    actions: {
        change
    },

    reducers: {
        subdomain: [findDefaultActiveSubdomain(), {



        }]
    }

})


function findDefaultActiveSubdomain() {
    return blogs.length ? blogs[0].subdomain : null;
}