import { atom } from 'recoil';

const blogsState = atom({
    key: 'blogs',
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
    default: [],
});

export default blogsState;