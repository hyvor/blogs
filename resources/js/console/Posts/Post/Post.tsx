import React, { useRef } from 'react';
import Loader from '../../ReusableComponents/Loader';
import { usePostValues, usePostActions } from "./helpers";
import useSave from './useSave'
import postsLogic from "../../logic/postsLogic";
import pagesLogic from "../../logic/pagesLogic";
import PostLeft from './New/PostLeft';
import PostRight from './New/PostRight';
import { CaretLeftFill } from 'react-bootstrap-icons';
import { useMountedLogic } from "kea";
import gptLogic from "../../logic/gptLogic";
import Discarder from './Discarder';

export default function Post({ id, subdomain, type }: { id: number, subdomain: string, type: string }) {

    // mount logic
    useMountedLogic(gptLogic({id}))

    const { loadPostAjax } = usePostValues(id);
    const postsLogicInst = postsLogic({ subdomain });
    const pagesLogicInst = pagesLogic({ subdomain });
    const { savePost } = usePostActions(id)

    const postViewRef = useRef<HTMLDivElement>(null);

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

    return <div className="new-post-view" id="post-view" ref={postViewRef}>

        <div className="post-inner">
            <PostLeft id={id} postViewRef={postViewRef} />
            <PostRight id={id} />
            <Discarder id={id} />
        </div>

        <button className="icon-button back-button" onClick={() => saveAndNavigateToList()} >
            <CaretLeftFill />
        </button>

    </div>

}
