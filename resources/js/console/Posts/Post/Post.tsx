import React from 'react';
import Loader from '../../ReusableComponents/Loader';
import Tooltip from '../../ReusableComponents/Tooltip';
import { usePostValues, usePostActions } from "./helpers";
import useSave from './useSave'
import PostTop from "./PostTop/PostTop";
import PostBottom from "./PostBottom";
import PostMiddle from "./PostMiddle";
import Unpublisher from "./Unpublisher";
import postsLogic from "../../logic/postsLogic";
import pagesLogic from "../../logic/pagesLogic";

export default function Post({ id, subdomain, type }: { id: number, subdomain: string, type: string }) {

    const { loadPostAjax, editorState } = usePostValues(id);
    const postsLogicInst = postsLogic({ subdomain });
    const pagesLogicInst = pagesLogic({ subdomain });
    const { savePost } = usePostActions(id)

    useSave(id);

    if (loadPostAjax.status === 'loading') {
        return <div className="post-loading">
            <Loader />
        </div>;
    }

    const saveAndNavigateToList = () => {
        savePost();
        if (type === 'post')
            postsLogicInst.actions.navigateToPosts();
        else
            pagesLogicInst.actions.navigateToPages();
    }

    return <div className={"post-editor fullscreen"}>

        <div className="pos-rel">
            <button className="icon-button back-button" onClick={() => saveAndNavigateToList()} >
                &times;
            </button>
            <PostTop id={id} />
            <PostMiddle id={id} />
            <PostBottom id={id} />


            <Unpublisher id={id} />

            <Tooltip place="bottom" />

        </div>

    </div >

}
