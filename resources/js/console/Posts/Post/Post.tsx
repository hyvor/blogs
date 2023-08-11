import React, { useRef } from 'react';
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
import Discarder from './Discarder';
import PostLeft from './New/PostLeft';
import PostRight from './New/PostRight';
import { CaretLeft, CaretLeftFill } from 'react-bootstrap-icons';

export default function Post({ id, subdomain, type }: { id: number, subdomain: string, type: string }) {

    const { loadPostAjax, editorState } = usePostValues(id);
    const postsLogicInst = postsLogic({ subdomain });
    const pagesLogicInst = pagesLogic({ subdomain });
    const { savePost } = usePostActions(id)

    const postViewRef = useRef<HTMLDivElement>(null);

    // useSave(id);

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

    return <div className="new-post-view" ref={postViewRef}>

        <div className="post-inner">
            <PostLeft id={id} postViewRef={postViewRef} />
            <PostRight id={id} />
        </div>

        <button className="icon-button back-button" onClick={() => saveAndNavigateToList()} >
            <CaretLeftFill />
        </button>

    </div>

    return <div className={"post-editor fullscreen"}>

        <div className="pos-rel">
            <button className="icon-button back-button" onClick={() => saveAndNavigateToList()} >
                &times;
            </button>
            <PostTop id={id} />
            <PostMiddle id={id} />
            <PostBottom id={id} />


            <Unpublisher id={id} />
            <Discarder id={id} />

            <Tooltip place="bottom" />

        </div>

    </div >

}
