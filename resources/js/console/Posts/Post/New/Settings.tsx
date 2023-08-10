import React, { Fragment, ReactNode, useEffect, useState } from "react";
import { usePostActions, usePostValues } from "../helpers";
import PostAuthors from "../PostAuthors";
import PostTags from "../PostTags";
import ReactDatePicker from "react-datepicker";
import dayjs from "dayjs";
import Checkbox from "../../../ReusableComponents/Checkbox";
import { Trash } from "react-bootstrap-icons";
import CodemirrorEditor from "../../../ReusableComponents/CodemirrorEditor";
import Button from "../../../ReusableComponents/Button";
import { Post, PostVariant } from "../../../types";
import { PopupConfirm } from "../../../ReusableComponents/Popup";
import Loader from "../../../ReusableComponents/Loader";


const settingToReadable = {
    authors: 'Authors',
    tags: 'Tags',
    canonical_url: 'Canonical URL',
    code_head: 'Code Head',
    code_foot: 'Code Foot',
    published_at: 'Published At',
    is_featured: 'Featured',
    featured_image_url: 'Cover Image',
    description: 'Description',
    slug: "Slug",
} as Record<keyof Post | keyof PostVariant, string>;


export default function Settings({id}: {id: number}) {

    const { post, postOriginal, currentLanguage, currentVariant, diff } = usePostValues(id);
    const {
        updatePostValue, updateCurrentPostVariantValue,
        updatePost, updateCurrentPostVariant,
        savePostDiff,
        deletePost, deleteVariant,
        changeEditorState
    } = usePostActions(id);


    const [settingsType, setSettingsType] = useState<'basic' | 'advanced'>('basic');
    const [isDiscarding, setIsDiscarding] = useState(false);
    const [isSaving, setIsSaving] = useState(false);

    const [codemirrorUpdateId, setCodemirrorUpdateId] = useState(0);

    const diffKeys = [
        'authors', 
        'tags', 
        'canonical_url', 
        'code_head', 
        'code_foot', 
        'published_at',
        'is_featured',
        'featured_image_url',
    ] as (keyof Post)[];

    const diffVariantKeys = [
        'description',
        'slug',
    ] as (keyof PostVariant)[];

    const changedKeys = diffKeys.filter(key => diff[key] !== undefined);
    const diffVariant = diff.variants ? diff.variants.find(v => v.language_id === currentLanguage.id) : null;
    const changedVariantKeys = diffVariantKeys.filter(key => diffVariant && diffVariant[key] !== undefined);
    const changedAllKeys = [...changedKeys, ...changedVariantKeys];

    function handleDiscardChanges() {
        const postUpdate = {} as Partial<Post>;
        const variantUpdate = {} as Partial<PostVariant>;

        changedKeys.forEach(key => (postUpdate as any)[key] = postOriginal[key]);
        changedVariantKeys.forEach(
            key => 
            (variantUpdate as any)[key] = postOriginal.variants.find(v => v.language_id === currentLanguage.id)![key]
        );

        console.log(postUpdate, variantUpdate);

        updatePost(postUpdate);
        updateCurrentPostVariant({...variantUpdate, language_id: currentLanguage.id});

        setIsDiscarding(false);
        setCodemirrorUpdateId(codemirrorUpdateId + 1);
    }

    function handleSave() {
        setIsSaving(true);

        const postDiff = {} as Partial<Post>;

        changedKeys.forEach(key => (postDiff as any)[key] = diff[key]);

        if (changedVariantKeys.length > 0) {
            const variant = {} as Partial<PostVariant>;
            variant.language_id = currentLanguage.id;
            changedVariantKeys.forEach(key => (variant as any)[key] = diffVariant![key]);
            postDiff.variants = [variant as PostVariant];
        }

        savePostDiff({
            diff: postDiff,
            onSave: () => {
                setIsSaving(false);
            }
        });
    }

    useEffect(() => {

        function handleClose(event: BeforeUnloadEvent) {
            if (changedAllKeys.length > 0) {
                event.preventDefault();
                event.returnValue = '';
            }
        }

        window.addEventListener('beforeunload', handleClose);
        return () => {
            window.removeEventListener('beforeunload', handleClose);
        }

    }, []);

    return <Fragment>

        <div className="toolbar-content">

            <div className="post-settings-wrap" data-testid="post-settings">

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
                                data-testid="slug-input"
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
                                data-testid="description-input"
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
                                <div data-testid="publish-time-input-wrap">
                                    <ReactDatePicker
                                        selected={dayjs.unix(post.published_at as number).toDate()}
                                        onChange={(date: Date) => updatePostValue("published_at", dayjs(date).unix())}
                                        showTimeInput
                                        dateFormat="yyyy-MM-dd h:mm aa"
                                    />
                                </div>
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
                                testId="featured-checkbox"
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
                                    data-testid="canonical-url-input"
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
                                    props={{
                                        "data-testid": "code-head-input"
                                    }}
                                    id={codemirrorUpdateId}
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
                                    props={{
                                        "data-testid": "code-foot-input"
                                    }}
                                    id={codemirrorUpdateId}
                                />
                            </Setting>

                    </Fragment>
                
                
                }

            </div>

        </div>

        {

            (changedKeys.length > 0 || changedVariantKeys.length > 0) &&

            <div className="toolbar-footer post-settings-save">
                
                <div className="changed">
                    <b>Changed:</b>&nbsp;
                    {
                        changedAllKeys
                            .map((key, i: number) => {
                                return <span key={key}>
                                    {settingToReadable[key] || key}
                                    {i < changedAllKeys.length - 1 ? ', ' : ''}
                                </span>
                            })
                    }
                </div>

                <div className="save-discard">

                    <Button onClick={() => setIsDiscarding(true)} type="text-only">
                        Discard
                    </Button>

                    <Button onClick={handleSave}>
                        Save Settings
                        { isSaving && <Loader size="mini" inline={true} color="#fff" /> }
                    </Button>

                </div>

            </div>

        }

        {
            isDiscarding &&

            <div data-testid="discard-popup">
                <PopupConfirm
                    title="Discard Changes?"
                    text="Are you sure you want to discard your changes?"
                    name="Discard"
                    buttonClass="danger"
                    onClick={handleDiscardChanges}
                    onCancel={() => setIsDiscarding(false)}
                />
            </div>
        }

    </Fragment>

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