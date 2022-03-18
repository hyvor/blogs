import { useActions, useValues } from 'kea';
import React, { useEffect, useRef, useState } from 'react';
import { BoxArrowUpRight, CaretDownFill, Fullscreen, GearFill, InfoCircle, PencilFill } from 'react-bootstrap-icons';
import Editor from './ProseMirror/Editor';
import TextareaAutosize from 'react-textarea-autosize';
import onOutsideClick from '../../helpers/onOutsideClick';
import postLogic from '../logic/postLogic';
import { getBlogUrl } from '../lib/blog-helpers';
import PostSettings from './PostSettings';
import Loader from '../ReusableComponents/Loader';
import { PopupConfirm } from '../ReusableComponents/Popup';
import PostPublisher from './PostPublisher';
import { toast } from 'react-toastify';
import ActionButton from '../ReusableComponents/ActionButton';
import languagesLogic from '../logic/languagesLogic';
import PostLanguageSelector from './PostLanguageSelector';
import blogsLogic from '../logic/blogsLogic';


let publisherOutsideCleaner;
export default function Post( {subdomain, id} ) {

    id = parseInt(id)

    const postLogicInst = postLogic({id});
    const { post, loadPostAjax, savePostAjax, forceSavePostAjax, getDiff } = useValues(postLogicInst)
    const { updatePostValue, updatePostVariantValue, savePost, forceSavePost } = useActions(postLogicInst)

    const { findBlogBySubdomain } = useValues(blogsLogic)

    const { languages } = useValues(languagesLogic({subdomain}))
 
    const [isFullScreen, setIsFullScreen] = useState(false);

    const [currentLanguageId, setCurrentLanguageId] = useState( findBlogBySubdomain(subdomain).blog.default_language.id );

    const variants = post.variants || [];
    const variant = variants.find(variant => variant.language_id === currentLanguageId) || {};

    /**
     * Disallow outside clicking when the content has changed
     */
    const viewRef = useRef(null);

    function handleAutoSave() {
        if (!holdAutoSavingRef.current) {
            savePost();
        }
    }

    // saving
    useEffect(() => {

        // auto save
        const autoSaveInterval = setInterval(handleAutoSave, 10000);

        function checkSave(e) {
            if (e.keyCode === 83 && (e.ctrlKey || e.metaKey)) { // ctrl + s
                handleAutoSave();
                e.preventDefault();
            }
        }

        function checkSaveUnload() {
            if (
                (post.status === 'published' || post.status === 'scheduled') && 
                Object.keys(getDiff()).length > 0
            ) {
                return true;
            } else {
                handleAutoSave();
            }
        }

        // save on CTRL + S
        window.addEventListener('keydown', checkSave);
        // save on unload
        window.addEventListener('beforeunload', checkSaveUnload);

        // save on outsideClick
        const removeOutsideEvent = onOutsideClick(viewRef.current, handleAutoSave, false, false, false);

        return () => {
            clearInterval(autoSaveInterval)
            window.removeEventListener('keydown', checkSave);
            window.removeEventListener('beforeunload', checkSaveUnload);
            removeOutsideEvent(false);
        }
    }, [id])

    /**
     * Settings view
     * Close on outside click
     */
    const [isSettingsOpen, setIsSettingsOpen] = useState(false);
    const settingsViewRef = useRef(null);

    /**
     * Publishing view
     */
    const [isPublisherOpen, setIsPublisherOpen] = useState(false);
    const publisherViewRef = useRef(null);

    function openSettingsView() {
        setIsSettingsOpen(true);
        onOutsideClick(settingsViewRef.current, closeSettingsView);
    }
    function closeSettingsView() {
        setIsSettingsOpen(false);
    }

    function openPublisher() {
        setIsPublisherOpen(true)
        publisherOutsideCleaner = onOutsideClick(publisherViewRef.current, closePublisher);
    }
    function closePublisher() {
        setIsPublisherOpen(false)
        publisherOutsideCleaner && publisherOutsideCleaner()
    }

    function UnPublishButton() {
        let name;
        if (post.status === 'published') {
            name = "Unpublish";
        } else if (post.status === 'scheduled') {
            name = "Unschedule";
        }

        return name ? <button 
            className="button small secondary unpublish-button"
            onClick={() => setIsUnPublishing(true)}
        >
            {name}
        </button> : null;
    }

    function MainButton() {
        let name, onClick, icon;

        if (post.status === 'published' || post.status === 'scheduled') {
            if (!nonDraftPostEditing) {
                name = "Edit Post";
                icon = <PencilFill />
                onClick = () => setNonDraftPostEditing(true);
            } else {
                return <ActionButton 
                    className="small main-button"
                    status={!isNonDraftUpdating ? "stale" : forceSavePostAjax.status} 
                    staleName="Update"
                    loadingName="Updating" 
                    successName="Updated"
                    errorName="Try again"
                    staleOnClick={handleUpdateNonDraft}
                    errorOnClick={handleUpdateNonDraft}
                />
            }
        } else if (post.status === 'draft') {
            name = "Publish Post";  
            onClick = openPublisher;
            icon = <CaretDownFill />;
        }

        return <button className="button small main-button" onClick={() => onClick()}>
            <span>{name}</span>{icon}
        </button>
    }

    function handleUpdateNonDraft() {
        setIsNonDraftUpdating(true);
        forceSavePost({
            update: {
                content: post.content_unsaved
            },
            onSave: (p) => {
                setIsNonDraftUpdating(false)
                toast.success(<div>Post Updated. <a className="link" href={p.url} target="_blank">View</a></div>, {
                    autoClose: 5000
                })
            }
        });
    }

    function toggleFullscreen() {

        if (!isFullScreen) {
            setIsFullScreen(true);
            window.addEventListener("keyup", checkFullscreenClose)
        } else {
            closeFullscreen();
        }

    }
    function closeFullscreen() {
        window.removeEventListener("keyup", checkFullscreenClose);
        setIsFullScreen(false);
    }
    function checkFullscreenClose(e) {
        if (e.key === "Escape")
            closeFullscreen();
    }

    const isNotDraft = post.status !== 'draft';
    const content = isNotDraft ? (post.content_unsaved || post.content) : post.content;

    // have to first click Edit Post to edit published/scheduled posts
    const [nonDraftPostEditing, setNonDraftPostEditing] = useState(false);
    const [isUnPublishing, setIsUnPublishing] = useState(false);
    const [isNonDraftUpdating, setIsNonDraftUpdating] = useState(false);    

    const holdAutoSavingRef = useRef(null); // for setInterval

    useEffect(() => {
        holdAutoSavingRef.current = isPublisherOpen || isUnPublishing || isNonDraftUpdating
    }, [isPublisherOpen, isUnPublishing, isNonDraftUpdating])


    function handleContentUpdate(v) {
        updatePostValue(isNotDraft ? 'content_unsaved' : 'content', v);
    }

    function handleUnPublish() {
        forceSavePost({
            update: {
                status: 'draft',
            },
            onSave: () => {
                toast("Post unpublished")
            }
        });
        setIsUnPublishing(false);
    }

    useEffect(() => {
        if (loadPostAjax.status === 'success') {
            setNonDraftPostEditing(isNotDraft && post.content_unsaved)
        }
    }, [loadPostAjax.status])

    if (loadPostAjax.status === 'loading') {
        return <div className="post-loading">
            <Loader />
        </div>;
    }

    return <div className={"post-editor" + (isFullScreen ? " fullscreen" : "") } ref={viewRef}>

        <div className="pos-rel"> {/* this element is required to make the tooltip work correctly */}
            <div className="post-editor-top">

                <div className="post-editor-top-content">

                    <PostLanguageSelector
                        id={id}
                        languages={languages} 
                        variants={variants}
                        currentLanguageId={currentLanguageId}
                        onChange={setCurrentLanguageId}
                    />

                    <div className="post-editor-title-row">

                        <div className="title-textarea-wrap">
                            <TextareaAutosize 
                                className="post-editor-title" 
                                placeholder="Title..."
                                value={variant.title || ""}
                                onChange={(e) => updatePostValue('title', e.target.value)}
                            />
                        </div>

                        <div className="status">
                            <span>{post.status}</span>
                        </div>

                    </div>

                    <div className="post-editor-settings">

                        <div className="post-editor-settings-buttons">
                            <div className="left">
                                <button 
                                    className={"button small" + (!isSettingsOpen ? " secondary" : " inactive")}
                                    onClick={isSettingsOpen ? null : openSettingsView}    
                                >
                                    <span>Settings</span><GearFill />
                                </button>

                                <a href={ getBlogUrl(subdomain, '/p/' + post.preview_id) } target="_blank">
                                    <button className="button small secondary view" >
                                        <span>View</span><BoxArrowUpRight />
                                    </button>
                                </a>
                                <button 
                                    className={"button small" + (!isFullScreen ? " secondary" : " inactive")}
                                    onClick={toggleFullscreen}
                                >
                                    <Fullscreen />
                                </button>
                            </div>

                            <div className="publish-buttons">
                                <UnPublishButton />
                                <MainButton />

                                <PostPublisher
                                    id={id}
                                    publisherViewRef={publisherViewRef}
                                    isOpen={isPublisherOpen}
                                    closePublisher={closePublisher}
                                />
                            </div>
                        </div>

                        <PostSettings 
                            isSettingsOpen={isSettingsOpen}
                            settingsViewRef={settingsViewRef}
                            id={id}
                        />

                    </div>
                
                </div>
                
            </div>

            <div 
                className="post-editor-wrap"
                spellCheck={false}
                onClick={() => false && view && view.focus()}
            >
                {
                    loadPostAjax.status === 'loading' ? null :
                    <Editor 
                        id={id}
                        value={content}
                        onChange={v => handleContentUpdate(v)}
                        editable={post.status === 'draft' || nonDraftPostEditing}
                    />
                }
            </div>

            <div
                className="post-editor-bottom"
            >
                <div className="post-editor-bottom-content">
                    <div id="pm-navigator-wrap"></div>
                    <div className="right">
                        {
                            savePostAjax.status === 'loading' ?
                            <span className="saving">Saving...</span> : null
                        }
                        <span className="words" id="pm-word-count"></span>
                        <a target="_blank" href="/docs/editor" className="help">
                            <InfoCircle />
                        </a>
                    </div>
                </div>
            </div>

        </div>
        
        {
            isUnPublishing ?
            <PopupConfirm 
                title={( post.status === 'published' ? 'Unpublish' : 'Unschedule' ) + " Post"}
                text={"Are you sure to " + ( post.status === 'published' ? 'unpublish' : 'unschedule' ) + " this post? It will be changed to a draft."}
                name={( post.status === 'published' ? 'Unpublish' : 'Unschedule' )}
                onClick={handleUnPublish}
                onCancel={() => setIsUnPublishing(false)}
            /> : null
        }

    </div>

}