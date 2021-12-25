import _globalState from './_globalState';

export default function useBlogsState() {

    /**
         * Array of Blog objects
         * 
         * {
         *      id:
         *      role: owner | admin | finance | editor | author | contributor
         *      name:
         *      subdomain:
         *      plan: null | personal_pro | team | enterprise
         * }
     */
    return _globalState('blogs', window.appConfig.blogs, {

        getBlogById: (id, blogs) => {
            for (let blog of blogs) {
                if (blog.id == id)
                    return blog;
            }
            return null;
        },

        getBlogBySubdomain: (subdomain, blogs) => {
            for (let blog of blogs) {
                if (blog.subdomain == subdomain)
                    return blog;
            }
            return null;
        }

    });

}