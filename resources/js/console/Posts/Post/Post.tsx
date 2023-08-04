import React, { useEffect, useState } from 'react';
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
import usersLogic from '../../logic/usersLogic';
import { Post as PostType } from "../../types";
import userBlogsLogic from '../../logic/userBlogsLogic';
import { PopupConfirm, PopupNotice } from '../../ReusableComponents/Popup';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export default function Post({ id, subdomain, type }: { id: number, subdomain: string, type: string }) {

    const { loadPostAjax, editorState } = usePostValues(id);
    const postsLogicInst = postsLogic({ subdomain });
    const pagesLogicInst = pagesLogic({ subdomain });
    const { savePost, forceSavePost } = usePostActions(id);
    const { post } = useValues(postLogic({ id }));
    const { users } = useValues(usersLogic({ subdomain }));

    const [showEditPopup, setShowEditPopup] = useState(false);
    const [postEditor, setPostEditor] = useState(post.editing_user);
    
    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    let activeBlog = findBlogBySubdomain(subdomain);

    useSave(id);

    const updatePostEditorId = () => {
        const update = {
            editing_user_id: Object.values(users)[0].id
        } as Partial<PostType>

        forceSavePost({
            update,
            onSave: () => {console.log('Post ' + post.id + ' under editing');}
        });
    }

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

    useEffect(() => {
        // Put the post under editing when the page is loaded
        updatePostEditorId();

        const resetPostEditorIdOnClose = () => {
            resetPostEditorId();
        };
    
        window.addEventListener('beforeunload', resetPostEditorIdOnClose);
    
        return () => {
            window.removeEventListener('beforeunload', resetPostEditorIdOnClose);
        };
    }, []);

    // Dyanmic post lock
    useEffect(() => {
        const blogId = activeBlog.blog.id;

        window.Pusher = Pusher;

        const echo = new Echo({
            broadcaster: 'pusher',
            key: 'app-key',//process.env.VITE_PUSHER_APP_KEY,
            wsHost: 'localhost',//process.env.VITE_PUSHER_HOST,
            wsPort: '6001',//process.env.VITE_PUSHER_PORT,
            wssPort: '6001',//process.env.VITE_PUSHER_PORT,
            forceTLS: false,
            encrypted: true,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            cluster: 'eu',
        });

        echo.channel(`blog.${blogId}`).listen('PostEditingUserChangedBroadcast', (e: any) => {
            console.log('here');
            if (e.postId === post.id) {
                if (e.user !== null && e.user.id == Object.values(users)[0]!.id) {
                    setShowEditPopup(true);
                    setPostEditor(e.user);
                }
            }
        });
        
    }, [activeBlog]);

    console.log(activeBlog);

    const PostEditingConflictPopup = () => <PopupNotice 
                                        title={'Editing conflict'}
                                        text={`${postEditor?.variants[0].name || 'Someone'} is editing this post. To avoid overwriting their changes, please quit editing.`} 
                                        name={'Quit editing'} 
                                        onClick={() => window.location = window.location.origin + '/console/'  + activeBlog.blog.name + '/posts'}
                                        />

    return <div className={"post-editor fullscreen"}>

        {showEditPopup && <PostEditingConflictPopup />}

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

