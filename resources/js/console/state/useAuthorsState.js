import _globalState from "./_globalState";

/**
 * 
 * Manages author data of a blog globally
 * 
 */
export default function useAuthorState(blogSubdomain) {

    const state = _globalState(`${blogSubdomain}:authors`, [], {

        getById: (id) => {
            return state.get().find(a => a.id == id);
        },

        add: () => {},
        remove: () => {},

    });

    function findDefaultActiveSubdomain() {
        return blogs.length ? blogs[0].subdomain : null;
    }

    return state;

}
