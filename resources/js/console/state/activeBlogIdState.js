import { atom } from 'recoil';

const activeBlogIdState = atom({
    key: 'activeBlogId',
    default: null,
});

export default activeBlogIdState;