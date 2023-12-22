import React, { useEffect, useRef } from 'react';
import Loader from '../../ReusableComponents/Loader';
import { usePostValues, usePostActions } from "./helpers";
import useSave from './useSave'
import postsLogic from "../../logic/postsLogic";
import pagesLogic from "../../logic/pagesLogic";
import PostLeft from './New/PostLeft';
import PostRight from './New/PostRight';
import { CaretLeftFill } from 'react-bootstrap-icons';
import { useMountedLogic, useValues } from "kea";
import gptLogic from "../../logic/gptLogic";
import Discarder from './Discarder';

interface Props { 
    id: number, 
    subdomain: string, 
    type: string 
}

export default function Post(props : Props) {

    const postsLogicInst = postsLogic({ subdomain: props.subdomain });
    const { hasPostLoaded } = useValues(postsLogicInst)

    const postLoaded = hasPostLoaded(props.id);

    useEffect(() => {
        if (!postLoaded) {
            postsLogicInst.actions.loadPost({id: props.id});
        }
    }, [postLoaded])

    if (!postLoaded) {
        return <div className="post-loading">
            <Loader />
        </div>;
    }

    return <PostInner {...props} />

}

function PostInner({ id, subdomain, type } : Props) {

    // mount logic
    useMountedLogic(gptLogic({id}))

    const { loadPostAjax } = usePostValues(id);
    const postsLogicInst = postsLogic({ subdomain });
    const pagesLogicInst = pagesLogic({ subdomain });

    const postViewRef = useRef<HTMLDivElement>(null);

    const { onBack } = useSave(id);

    if (loadPostAjax.status === 'loading') {
        return <div className="post-loading">
            <Loader />
        </div>;
    }

    async function handleBack() {
        await onBack();
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

        <button className="icon-button back-button" onClick={handleBack} >
            <CaretLeftFill />
        </button>

    </div>

}