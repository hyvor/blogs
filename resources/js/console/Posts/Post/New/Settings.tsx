import React, { Fragment, ReactNode, useState } from "react";
import { usePostActions, usePostValues } from "../helpers";
import PostAuthors from "../PostAuthors";
import PostTags from "../PostTags";
import ReactDatePicker from "react-datepicker";
import dayjs from "dayjs";
import Checkbox from "../../../ReusableComponents/Checkbox";
import { Trash } from "react-bootstrap-icons";
import CodemirrorEditor from "../../../ReusableComponents/CodemirrorEditor";


export default function Settings({id}: {id: number}) {

    const { post, editorState, currentLanguage, currentVariant } = usePostValues(id);
    const {
        updatePostValue, updateCurrentPostVariantValue,
        deletePost, deleteVariant,
        savePost,
        changeEditorState
    } = usePostActions(id);


    const [settingsType, setSettingsType] = useState<'basic' | 'advanced'>('basic');

    return <div className="post-settings-wrap">

        

        <div className="setting-select">
            <button 
                onClick={() => setSettingsType('basic')} 
                className={settingsType === 'basic' ? 'active' : ''}
            >Basic</button>
            <button 
                onClick={() => setSettingsType('advanced')} 
                className={settingsType === 'advanced' ? 'active' : ''}
            >Advanced</button>
        </div>

        {
            settingsType === 'basic' ?
            <Fragment>

                <Setting title="Slug">
                    <input
                        className="input"
                        value={currentVariant.slug || ''}
                        onChange={(e) => updateCurrentPostVariantValue("slug", e.target.value)}
                        maxLength={250}
                    />
                </Setting>

                <Setting 
                    title="Description"
                    description="Summarization of the post for listing pages and search engines."
                    className="post-setting-description"
                >
                    <textarea
                        className="input description-input"
                        placeholder="Write a description..."
                        value={currentVariant.description || ''}
                        onChange={e => updateCurrentPostVariantValue('description', e.target.value)}
                        maxLength={350}
                    />
                </Setting>

                <Setting 
                    title="Authors"
                    description="The unique part of the URL to identify this post"
                >
                    <PostAuthors post={post} updatePostValue={updatePostValue} />
                </Setting>

                <Setting 
                    title="Tags"
                >
                    <PostTags post={post} updatePostValue={updatePostValue} />
                </Setting>


                <Setting 
                    title="Cover Image"
                    className="post-setting-featured-image"
                >
                    <div className="featured-image">
                        <div className="no-image">Upload a file</div>
                    </div>
                </Setting>

                <Setting 
                    title="Publish Time"
                    className="publish-time"
                >
                    {
                        currentVariant.status !== 'published' && currentVariant.status !== 'scheduled' ?
                        <div className="not-published">Not published</div> :
                        <ReactDatePicker
                            selected={dayjs.unix(post.published_at as number).toDate()}
                            onChange={(date: Date) => updatePostValue("published_at", dayjs(date).unix())}
                            showTimeInput
                            dateFormat="yyyy-MM-dd h:mm aa"
                        />
                    }
                </Setting>

                <Setting 
                    title="Featured?"
                    description="The unique part of the URL to identify this post"
                    className="has-top-padding"
                >
                    <Checkbox
                        checked={post.is_featured}
                        onChange={(featured: boolean) => updatePostValue('is_featured', featured)}
                    />
                </Setting>

                <Setting 
                    title={currentLanguage.is_primary ? "Delete Post" : `Delete ${currentLanguage.name} Variant`}
                    className="has-top-padding"
                >
                    <button 
                        className="button small danger"
                        //onClick={() => setIsDeleting(true)}
                    >Delete <Trash /></button>
                </Setting>

        </Fragment> :
        
            <Fragment>

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
                        className="code"
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
                        className="code"
                    >
                        <CodemirrorEditor
                            extension="twig"
                            value={post.code_foot || ''}
                            onChange={(val: string) => updatePostValue('code_foot', val)}
                        />
                    </Setting>

            </Fragment>
        
        
        }

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

        <div className="post-setting-left">
            <div className="post-setting-title">{title}</div>
            <div style={{display: "none"}} className="post-setting-description">{description}</div>
        </div>

        <div className="post-setting-content">
            {children}
        </div>
    </div>

);