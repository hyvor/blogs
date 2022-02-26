import { useActions, useValues } from 'kea';
import React, { useEffect, useRef, useState } from 'react';
import { BoxArrowUpRight, CaretDownFill, Fullscreen, GearFill, PencilFill } from 'react-bootstrap-icons';
import Editor from './ProseMirror/Editor';
import TextareaAutosize from 'react-textarea-autosize';
import onOutsideClick from '../../helpers/onOutsideClick';
import postLogic from '../logic/postLogic';
import { getBlogUrl } from '../lib/blog-helpers';
import PostSettings from './PostSettings';

export default function Post( {subdomain, id} ) {

    id = parseInt(id)

    const postLogicInst = postLogic({id});
    const { post, loadPostAjax, savePostAjax, getDiff } = useValues(postLogicInst)
    const { updatePostValue, savePost, deletePost } = useActions(postLogicInst)


    const [isFullScreen, setIsFullScreen] = useState(false);


    /**
     * Disallow outside clicking when the content has changed
     */
    const viewRef = useRef(null);

    // saving
    useEffect(() => {

        // auto save
        const autoSaveInterval = setInterval(savePost, 10000);

        function checkSave(e) {
            if (e.keyCode === 83 && (e.ctrlKey || e.metaKey)) { // ctrl + s
                savePost();
                e.preventDefault();
            }
        }

        function checkSaveUnload() {
            if (
                (post.status === 'published' || post.status === 'scheduled') && 
                Object.keys(getDiff).length > 0
            ) {
                return true;
            } else {
                savePost();
            }
        }

        // save on CTRL + S
        window.addEventListener('keydown', checkSave);
        // save on unload
        window.addEventListener('beforeunload', checkSaveUnload);

        // save on outsideClick
        const removeOutsideEvent = onOutsideClick(viewRef.current, savePost, false, false, false);

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

    function openSettingsView() {
        setIsSettingsOpen(true);
        onOutsideClick(settingsViewRef.current, closeSettingsView);
    }
    function closeSettingsView() {
        setIsSettingsOpen(false);
    }


    // have to first click Edit Post to edit published/scheduled posts
    const [nonDraftPostEditing, setNonDraftPostEditing] = useState(false);

    function MainButton() {
        let name, onClick, icon;

        if (post.status === 'published' || post.status === 'scheduled') {
            if (!nonDraftPostEditing) {
                name = "Edit Post";
                icon = <PencilFill />
                onClick = () => setNonDraftPostEditing(true);
            } else {
                name  = "Update Post";
                onClick = () => showUpdateDetails();
            }
        } else if (post.status === 'draft') {
            name = "Publish Post";
            onClick = () => showPublishDetails()
        }

        return <button className="button small main-button" onClick={() => onClick()}>
            <span>{name}</span>{icon || <CaretDownFill />}
        </button>
    }

    function showUpdateDetails() {
        alert("Updated");
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

    return <div className={"post-editor" + (isFullScreen ? " fullscreen" : "") } ref={viewRef}>

        <div className="pos-rel"> {/* this element is required to make the tooltip work correctly */}
            <div className="post-editor-top">

                <div className="post-editor-top-content">

                    <div className="post-editor-title-row">

                        <div className="title-textarea-wrap">
                            <TextareaAutosize 
                                className="post-editor-title" 
                                placeholder="Title..."
                                value={post.title || ""}
                                onChange={(e) => updatePostValue('title', e.target.value)}
                            />
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
                                <MainButton />
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
                        value={post.content}
                        onChange={v => updatePostValue('content', v)}
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
                    </div>
                </div>
            </div>

        </div>
        

    </div>

}