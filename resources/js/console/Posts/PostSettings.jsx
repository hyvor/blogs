import { useActions, useValues } from 'kea';
import React, { useState, useRef } from 'react';
import { Trash } from 'react-bootstrap-icons';
import postLogic from '../logic/postLogic';
import CodemirrorEditor, { CODEMIRROR_MODES } from '../ReusableComponents/CodemirrorEditor';
import { PopupConfirm } from '../ReusableComponents/Popup';
import { toast } from 'react-toastify'
import mediaLogic from '../logic/mediaLogic';
import subdomainLogic from '../logic/subdomainLogic';
import Checkbox from '../ReusableComponents/Checkbox';
import dayjs from 'dayjs';
import DatePicker from 'react-datepicker';
import Loader from '../ReusableComponents/Loader';
import { usePostActions, usePostValues } from './usePost';

export default function PostSettings({ isSettingsOpen, settingsViewRef, id }) {

    const { post } = usePostValues(id);
    const { updatePostValue, deletePost, savePost } = usePostActions(id);

    const {subdomain} = useValues(subdomainLogic);
    const mediaLogicInst = mediaLogic({subdomain})
    const { uploadImageAjax } = useValues(mediaLogicInst);
    const { uploadImage } = useActions(mediaLogicInst)

    const [ isDeleting, setIsDeleting ] = useState(false);
    const [ isFeaturedImageRemoving, setIsFeaturedImageRemoving ] = useState(false);

    const imageUploadInputRef = useRef(null)

    function handleDelete() {
        deletePost({id})
    }

    function handleFeaturedImageRemove() {
        setIsFeaturedImageRemoving(false)
        updatePostValue("featured_image", null);
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
                                maxLength={250}
                            ></input>
                        </Setting>

                        <Setting 
                            title="Publish Time"
                            className="post-setting-publish-time"
                        >
                            {
                                post.status !== 'published' && post.status !== 'scheduled' ?
                                <div className="not-published">Not published</div> :
                                <DatePicker
                                    selected={dayjs.unix(post.published_at).toDate()}
                                    onChange={(date) => updatePostValue("published_at", dayjs(date).unix())}
                                    showTimeInput
                                    dateFormat="yyyy-MM-dd h:mm aa"
                                />
                            }
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
                                maxLength={350}
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
                                    uploadImageAjax.status === 'loading' ?
                                    <div className="no-image">
                                        <Loader />
                                    </div> :
                                    (
                                        post.featured_image ?
                                        <div className="image-preview">
                                            <img src={post.featured_image} />
                                            <span className="delete-button" onClick={(e) => {
                                                e.stopPropagation();
                                                setIsFeaturedImageRemoving(true)
                                            }}>
                                                <Trash size={10} />
                                            </span>
                                        </div>
                                        :
                                        <div className="no-image">Upload a file</div>
                                    )
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
                            <Checkbox 
                                checked={post.is_featured}
                                onChange={featured => updatePostValue('is_featured', featured)}
                            />
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
                            maxLength={250}
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

            {
                isFeaturedImageRemoving ?
                <PopupConfirm 
                    title="Remove Featured Image"
                    text={<div>
                        Are you sure to remove this featured image?
                    </div>}
                    onClick={handleFeaturedImageRemove}
                    onCancel={() => setIsFeaturedImageRemoving(false)}
                    name="Remove"
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