import React from 'react';
import Loader from '../../ReusableComponents/Loader';
import Tooltip from '../../ReusableComponents/Tooltip';
import { usePostValues } from "./helpers";
import useSave from './useSave'
import PostTop from "./PostTop/PostTop";
import PostBottom from "./PostBottom";
import PostMiddle from "./PostMiddle";
import Unpublisher from "./Unpublisher";
import postsLogic from "../../logic/postsLogic";

export default function Post({ id, subdomain }: { id: number, subdomain: string }) {

    const { loadPostAjax, editorState } = usePostValues(id);
    const postsLogicInst = postsLogic({ subdomain });

    useSave(id);

    if (loadPostAjax.status === 'loading') {
        return <div className="post-loading">
            <Loader />
        </div>;
    }

    return <div className={"post-editor fullscreen"}>

        <div className="pos-rel">
            <button className="button back-button" onClick={() => postsLogicInst.actions.navigateToPosts()} >
                Back
            </button>
            <PostTop id={id} />
            <PostMiddle id={id} />
            <PostBottom id={id} />


            <Unpublisher id={id} />

            <Tooltip place="bottom" />

        </div>

    </div >

}
