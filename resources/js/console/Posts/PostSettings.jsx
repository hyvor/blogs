import { useActions, useValues } from 'kea';
import React, { useState, useRef } from 'react';
import { Trash } from 'react-bootstrap-icons';
import postLogic from '../logic/postLogic';
import CodemirrorEditor, { CODEMIRROR_MODES } from '../ReusableComponents/CodemirrorEditor';
import { PopupConfirm } from '../ReusableComponents/Popup';
import { toast } from 'react-toastify'
import mediaLogic from '../logic/mediaLogic';
import subdomainLogic from '../logic/subdomainLogic';

export default function PostSettings({ isSettingsOpen, settingsViewRef, id }) {

    const postLogicInst = postLogic({id});
    const { post } = useValues(postLogicInst)
    const { updatePostValue, deletePost, savePost } = useActions(postLogicInst)

    const {subdomain} = useValues(subdomainLogic);
    const mediaLogicInst = mediaLogic({subdomain})
    const { uploadImage } = useActions(mediaLogicInst)

    const [ isDeleting, setIsDeleting ] = useState(false);

    const imageUploadInputRef = useRef(null)

    function handleDelete() {
        deletePost({id})
    }

    function handleUploadInputClick() {
        imageUploadInputRef.current.click();
    }
    function handleUpload(e) {
        const file = e.target.files[0]
        if (!file) {
            return toast.error("No files selected");
        }
        uploadImage({
            file,
            onUpload: (media) => {
                updatePostValue("featured_image", media.url)
                savePost()
            }
        })
    }

    const [ settingsType, setSettingsType ] = useState('basic'); // basic | advanced

    return <div className={"settings-view-wrap " + (isSettingsOpen ? "active" : "inactive") }>
        <div ref={settingsViewRef} className="post-editor-settings-view">

            <div className="setting-select">
                <span 
                    onClick={() => setSettingsType('basic')} 
                    className={settingsType === 'basic' ? 'active' : ''}
                >Basic</span>
                <span 
                    onClick={() => setSettingsType('advanced')} 
                    className={settingsType === 'advanced' ? 'active' : ''}
                >Advanced</span>
            </div>

            {
            settingsType === 'basic' ?
                <div className="setting-show">
                    <div className="post-setting-dual">
                        <Setting 
                            title="Slug"
                            description="The unique part of the URL to identify this post"
                        >
                            <input 
                                className="input" 
                                value={post.slug}
                                onChange={(e) => updatePostValue("slug", e.target.value)}
                            ></input>
                        </Setting>

                        <Setting 
                            title="Publish Time"
                            className="post-setting-featured-image"
                        >
                            <input className="input" value="2021-01-01" onChange={() => {}}></input>
                        </Setting>

                    </div>

                    <div className="post-setting-dual">

                        <Setting 
                            title="Authors"
                            description="The unique part of the URL to identify this post"
                        >
                            <input className="input" value="Ishini Avindya" onChange={() => {}}></input>
                        </Setting>

                        <Setting 
                            title="Tags"
                            className="post-setting-featured-image"
                        >
                            <input className="input" value="#creative" onChange={() => {}}></input>
                        </Setting>

                    </div>

                    <div className="post-setting-dual">

                        <Setting 
                            title="Description"
                            description="Summarization of the post for listing pages and search engines."
                            className="post-setting-description"
                        >
                            <textarea 
                                className="input"
                                placeholder="Write a description..."
                                value={post.description}
                                onChange={e => updatePostValue('description', e.target.value)}
                            ></textarea>
                        </Setting>

                        <Setting 
                            title="Featured Image"
                            className="post-setting-featured-image"
                        >
                            <div 
                                className="featured-image"
                                onClick={handleUploadInputClick}
                            >
                                {
                                    post.featured_image ?
                                    <img src={post.featured_image} /> :
                                    <div>Upload a file</div>
                                }
                                <input
                                    ref={imageUploadInputRef}
                                    type="file" 
                                    style={{display:'none'}} 
                                    accept="image/*"
                                    onChange={handleUpload}
                                />
                            </div>
                            
                        </Setting>

                    </div>

                    <div className="post-setting-dual">

                        <Setting 
                            title="Featured?"
                            description="The unique part of the URL to identify this post"
                        >
                            <input type="checkbox"></input>
                        </Setting>

                        <Setting 
                            title="Delete Post"
                        >
                            <button 
                                className="button small danger"
                                onClick={() => setIsDeleting(true)}
                            >Delete <Trash /></button>
                        </Setting>

                    </div>
                </div>
                : 
                <div className="setting-show">

                    <Setting 
                        title="Canonical URL"
                        description=""
                    >
                        <input 
                            className="input"
                            value={post.canonical_url}
                            onChange={e => updatePostValue('canonical_url', e.target.value)}
                        ></input>
                    </Setting>

                    <Setting 
                        title="Header HTML Code"
                        description="Summarization of the post for listing pages and search engines."
                        className="post-setting-description"
                    >
                        <CodemirrorEditor
                            mode={CODEMIRROR_MODES.twig}
                            value={post.code_head}
                            onChange={val => updatePostValue('code_head', val)}
                        />
                    </Setting>

                    <Setting 
                        title="Footer HTML Code"
                        description="Summarization of the post for listing pages and search engines."
                        className="post-setting-description"
                    >
                        <CodemirrorEditor
                            mode={CODEMIRROR_MODES.twig}
                            value={post.code_foot}
                            onChange={val => updatePostValue('code_foot', val)}
                        />
                    </Setting>
                </div>
            }


            {
                isDeleting ?
                <PopupConfirm 
                    title="Delete Post"
                    text={<div>
                        Are you sure to <b>permanently delete</b> this post? 
                    </div>}
                    onClick={handleDelete}
                    onCancel={() => setIsDeleting(false)}
                    name="Delete"
                    buttonClass="danger"
                /> : null
            }

        </div>


    </div>

}

function Setting(props) {

    return <div className={"post-setting " + (props.className || "")}>
        <div className="post-setting-title">{props.title}</div>
        { false ? <div className="post-setting-description">{props.description}</div> : null }

        <div className="post-setting-content">
            {props.children}
        </div>
    </div>

}