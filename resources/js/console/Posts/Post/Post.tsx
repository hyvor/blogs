import React, { useEffect } from 'react';
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
import postLogic from '../../logic/postLogic';
import { useValues } from 'kea';

export default function Post({ id, subdomain, type }: { id: number, subdomain: string, type: string }) {

    const { loadPostAjax, editorState } = usePostValues(id);
    const postsLogicInst = postsLogic({ subdomain });
    const pagesLogicInst = pagesLogic({ subdomain });
    const { savePost, forceSavePost } = usePostActions(id);
    const { post } = useValues(postLogic({ id }));

    useSave(id);

    if (loadPostAjax.status === 'loading') {
        return <div className="post-loading">
            <Loader />
        </div>;
    }

    const resetPostEditorId = () => {
        const update = {
            editing_user_id: null
        } as Partial<typeof Post>

        forceSavePost({
            update,
            onSave: () => {console.log('Post ' + post.id + ' no longer editing');}
        });
    }

    const saveAndNavigateToList = () => {
        savePost();
        if (type === 'post') {
            resetPostEditorId();
            postsLogicInst.actions.navigateToPosts();
        }
        else
            pagesLogicInst.actions.navigateToPages();
    }

    useEffect(() => {
        window.onpopstate = () => {
            resetPostEditorId();
        };
    });

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

