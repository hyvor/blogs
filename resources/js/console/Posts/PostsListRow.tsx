import React, { useEffect, useState } from 'react';
import NavLink from '../ReusableComponents/NavLink';
import postLogic from '../logic/postLogic';
import { useValues } from 'kea';
import dayjs from 'dayjs';
import languagesLogic from '../logic/languagesLogic';
import LangTag from '../ReusableComponents/LangTag';
import { getLangTagIconByPostStatus } from './Post/PostLanguageSelector';
import { Post, PostVariant } from "../types";
import UserPermissions from "../services/UserPermissions";
import usersLogic from '../logic/usersLogic';
import { usePostActions } from './Post/helpers';
import { Lock } from 'react-bootstrap-icons';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import userBlogsLogic from '../logic/userBlogsLogic';
import Toast from '../ReusableComponents/Toast';
import { toast } from 'react-toastify';
import Tooltip from '../ReusableComponents/Tooltip';
import ReactTooltip from 'react-tooltip';
import DualSetting from '../ReusableComponents/DualSetting';
import { PopupConfirm } from '../ReusableComponents/Popup';
import { useNavigate } from "react-router-dom";

export default function PostsListRow({ id, subdomain }: { id: number, subdomain: string }) {

    const { languages, getLanguageById } = useValues(languagesLogic({ subdomain }));
    const { post, postOriginal } = useValues(postLogic({ id }));
    const { forceSavePost } = usePostActions(id)
    const { users } = useValues(usersLogic({ subdomain }));
    const { findBlogBySubdomain } = useValues(userBlogsLogic);

    let activeBlog = findBlogBySubdomain(subdomain);

    const postsLink = `/console/${subdomain}/` + (post.is_page ? 'pages' : 'posts')
    const toLink = `${postsLink}/${post.id}`

    const languageId = languages[0].id

    const variant = post.variants.find(v => v.language_id === languageId) as PostVariant;

    const authorsNames = post.authors.map(author => author.variants[0].name).join(", ");

    const authorsImages = post.authors.map(author => author.picture_url);

    const permClass = UserPermissions.canEditPost(postOriginal) ? '' : 'global-no-permissions';

    const [postLock, setPostLock] = useState(post.editing_user ? post.editing_user.id !== Object.values(users)[0]!.id : false);

    const [postEditor, setPostEditor] = useState(post.editing_user);

    const [showLockPopup, setShowLockPopup] = useState(false);

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
            if (e.postId === post.id) {
                if (e.user == null)
                    setPostLock(false);
                else {
                    setPostLock(e.user.id == Object.values(users)[0]!.id);
                    setPostEditor(e.user);
                }
            }
        });
        
    }, [activeBlog]);

    const handlePostLinkClick = (e: any) => {
        if (postLock && !showLockPopup) {
            e.preventDefault();
            setShowLockPopup(true);
        } 
    };

    const forceEditPost = () => {
        const update = {
            editing_user_id: Object.values(users)[0].id
        } as Partial<Post>

        forceSavePost({
            update,
            onSave: () => {console.log('Post ' + post.id + ' under editing');}
        });
        window.location = location.pathname === toLink ? postsLink : toLink;
    }


    const PostEditingPopup = () => <PopupConfirm 
                                        title={'This post is under editing'}
                                        text={'This post is being edited by ' + postEditor?.variants[0].name + '. Do you want to force editing ?'} 
                                        name={'Force editing'} 
                                        onClick={() => forceEditPost()}
                                        onCancel={() =>setShowLockPopup(false)}/>

    return <NavLink
        key={post.id}
        href={location.pathname === toLink ? postsLink : toLink}
        onClick={handlePostLinkClick}
        disabled={postLock}
        className={"posts-list-item" + ` ${variant.status} ${permClass}`}>

        {showLockPopup && <PostEditingPopup />}

        <div>
            <div className="post-title">
                {variant.title || '(Untitled)'}
                    {postLock ? 
                        <div className='post-lock'>
                            <img className='editor-image' src={postEditor?.picture_url ?? ''}></img>
                            {`${postEditor?.variants[0].name}` ?? 'User'}&nbsp;
                            <div className='post-editing-text'> is editing</div>
                        </div>
                    : null}
            </div>

            <div className="post-data">
                <div className="post-date">
                    {dayjs.unix(post.published_at || post.created_at).format('MMM D, YYYY')}
                </div>
            </div>

        </div>

        <div className="post-languages">
            {
                post.variants.map(variant => {
                    const lang = getLanguageById(variant.language_id)

                    return lang ?
                        <LangTag
                            key={lang.id}
                            code={lang.code}
                            icon={getLangTagIconByPostStatus(variant.status)}
                        /> : null
                })
            }
        </div>

        {
            !post.is_page ?
                <div className="post-authors">
                    {
                        post.authors.map(author => {
                            return <div className="post-author">
                                <img
                                    src={author.picture_url || undefined}
                                    className="round-image-40 post-author-image "
                                    alt="Profile Picture"
                                />
                                <span className="post-author-name">
                                        {author.variants[0].name}
                                    </span>
                            </div>
                        })
                    }
                </div>
                : null
        }

        <div className="post-tags-wrap">

            <div className="post-tags">
                {
                    !post.is_page ?
                        post.tags.map(tag => {
                            return <span
                                key={tag.id}
                                className="post-tag"
                            >
                                {
                                    tag.variants.find(v => v.language_id === languageId)?.name
                                }
                            </span>
                        })
                        : null
                }
            </div>
        </div>
        <div className="post-status-wrap">
            <span className={`global-post-status ${variant.status}`}>{variant.status}</span>
        </div>


    </NavLink>

}