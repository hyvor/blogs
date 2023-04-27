import { useActions, useValues } from 'kea';
import React, {useState, useRef, ReactNode, useEffect} from 'react';
import { Trash } from 'react-bootstrap-icons';
import CodemirrorEditor, { CODEMIRROR_MODES } from '../../../ReusableComponents/CodemirrorEditor';
import { PopupConfirm } from '../../../ReusableComponents/Popup';
import { toast } from 'react-toastify'
import mediaLogic from '../../../logic/mediaLogic';
import Checkbox from '../../../ReusableComponents/Checkbox';
import dayjs from 'dayjs';
import DatePicker from 'react-datepicker';
import Loader from '../../../ReusableComponents/Loader';

import { usePostActions, usePostValues } from '../helpers';
import PostAuthors from "../PostAuthors";
import PostTags from "../PostTags";
import {Media, PostVariant} from "../../../types";
import getSubdomain from "../../../logic-helpers/subdomain";
import onOutsideClick from "../../../../helpers/onOutsideClick";
import languagesLogic from "../../../logic/languagesLogic";

type PostSettingsProps = {
    id: number;
};

let outsideClickListenerRemover: any;

export default function PostSettings({ id } : PostSettingsProps) {

    const { post, editorState, currentLanguage, currentVariant } = usePostValues(id);
    const {
        updatePostValue, updateCurrentPostVariantValue,
        deletePost, deleteVariant,
        savePost,
        changeEditorState
    } = usePostActions(id);

    const publisherViewRef = useRef<HTMLDivElement | null>(null)

    const mediaLogicInst = mediaLogic({subdomain: getSubdomain()})
    const { uploadImageAjax } = useValues(mediaLogicInst);
    const { uploadImage } = useActions(mediaLogicInst)

    const [ isDeleting, setIsDeleting ] = useState(false);
    const [ isFeaturedImageRemoving, setIsFeaturedImageRemoving ] = useState(false);

    const imageUploadInputRef = useRef<HTMLInputElement | null>(null)

    function handleDelete() {
        if (currentLanguage.is_primary) {
            deletePost()
        } else {
            toast(currentLanguage.name + " variant deleted");
            changeEditorState('languageId', languagesLogic({subdomain: getSubdomain()}).values.primaryLanguage.id)
            changeEditorState('isChangingSettings', false)
            setIsDeleting(false)
            deleteVariant({languageId: currentLanguage.id})
        }
    }

    function handleFeaturedImageRemove() {
        setIsFeaturedImageRemoving(false)
        updatePostValue("featured_image_url", null);
    }

    function handleUploadInputClick() {
        (imageUploadInputRef.current as HTMLInputElement).click();
    }
    function handleUpload(e: React.ChangeEvent<HTMLInputElement>) {
        const file = (e.currentTarget as any).files[0]
        if (!file) {
            return toast.error("No files selected");
        }
        uploadImage({
            file,
            onUpload: (media: Media) => {
                updatePostValue("featured_image_url", media.url)
                savePost()
            }
        })
    }

    const [ settingsType, setSettingsType ] = useState('basic'); // basic | advanced

    useEffect(() => {
        if (editorState.isChangingSettings) {
            outsideClickListenerRemover = onOutsideClick(
                publisherViewRef.current,
                () => changeEditorState('isChangingSettings', false)
            );
        } else {
            outsideClickListenerRemover && outsideClickListenerRemover()
        }
    }, [editorState.isChangingSettings])

    return <div className={"settings-view-wrap " + (editorState.isChangingSettings ? "active" : "inactive") }>
        <div ref={publisherViewRef} className="post-editor-settings-view">

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
                                value={currentVariant.slug || ''}
                                onChange={(e) => updateCurrentPostVariantValue("slug", e.target.value)}
                                maxLength={250}
                            />
                        </Setting>

                        <Setting 
                            title="Publish Time"
                            className="post-setting-publish-time"
                        >
                            {
                                currentVariant.status !== 'published' && currentVariant.status !== 'scheduled' ?
                                <div className="not-published">Not published</div> :
                                <DatePicker
                                    selected={dayjs.unix(post.published_at as number).toDate()}
                                    onChange={(date: Date) => updatePostValue("published_at", dayjs(date).unix())}
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
                            <PostAuthors post={post} updatePostValue={updatePostValue} />
                        </Setting>

                        <Setting 
                            title="Tags"
                            className="post-setting-featured-image"
                        >
                            <PostTags post={post} updatePostValue={updatePostValue} />
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
                                value={currentVariant.description || ''}
                                onChange={e => updateCurrentPostVariantValue('description', e.target.value)}
                                maxLength={350}
                            />
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
                                        post.featured_image_url ?
                                        <div className="image-preview">
                                            <img src={post.featured_image_url} alt="Featured Image" />
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
                                    onChange={e => handleUpload(e)}
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
                                onChange={(featured: boolean) => updatePostValue('is_featured', featured)}
                            />
                        </Setting>

                        <Setting 
                            title={currentLanguage.is_primary ? "Delete Post" : `Delete ${currentLanguage.name} Variant`}
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
                            value={post.canonical_url || ''}
                            onChange={e => updatePostValue('canonical_url', e.target.value)}
                            maxLength={250}
                        />
                    </Setting>

                    <Setting 
                        title="Head Code"
                        description="Summarization of the post for listing pages and search engines."
                        className="post-setting-description"
                    >
                        <CodemirrorEditor
                            extension="twig"
                            value={post.code_head || ''}
                            onChange={(val: string) => updatePostValue('code_head', val)}
                        />
                    </Setting>

                    <Setting 
                        title="Foot Code"
                        description="Summarization of the post for listing pages and search engines."
                        className="post-setting-description"
                    >
                        <CodemirrorEditor
                            extension="twig"
                            value={post.code_foot || ''}
                            onChange={(val: string) => updatePostValue('code_foot', val)}
                        />
                    </Setting>
                </div>
            }


            {
                isDeleting ?
                <PopupConfirm 
                    title="Delete Post"
                    text={currentLanguage.is_primary ?
                        <div>Are you sure to <b>permanently delete</b> this post?</div> :
                        <div>Are you sure to <b>permanently delete</b> the {currentLanguage.name} variant of this post?</div>
                    }
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

type SettingProps = {
    title: string,
    description?: string,
    className?: string,
    children: ReactNode
}

const Setting: React.FC<SettingProps> = ({ title, description, className, children }) => (

    <div className={"post-setting " + (className || "")}>
        <div className="post-setting-title">{title}</div>
        <div style={{display: "none"}} className="post-setting-description">{description}</div>

        <div className="post-setting-content">
            {children}
        </div>
    </div>

);