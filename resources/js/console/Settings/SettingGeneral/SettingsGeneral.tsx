import React, { useState, useRef} from 'react';
import { useActions, useValues } from 'kea';
import DualSetting from '../../ReusableComponents/DualSetting';
import Input from '../../ReusableComponents/Input';
import SettingsSave from '../../ReusableComponents/SettingsSave';

import subdomainLogic from '../../logic/subdomainLogic';
import blogLogic from '../../logic/blogLogic';
import languagesLogic from '../../logic/languagesLogic';
import GeneralLanguageSelector from './GeneralLanguageSelector';
import {BlogVariant} from "../../types";


// Should find a way to set up the should save section in the pop-up.
export default function SettingsGeneral() {

    const subdomain = subdomainLogic.values.subdomain;
    const blogLogicBuilt = blogLogic({subdomain})
    const { blog } = useValues(blogLogicBuilt)
    const { updateBlogValue, updateBlogVariantValue } = useActions(blogLogicBuilt)

    const { languages, primaryLanguage } = useValues(languagesLogic({subdomain}))
    const [currentLanguageId, setCurrentLanguageId] = useState( primaryLanguage.id );
    const variants = blog.variants;
    const variant = variants[currentLanguageId] || {} as BlogVariant;



    // FeatureImage and Icon section
    /*function handleFeatureImage() {
        if (!uploadInput) {
            uploadInput = document.createElement('input')
            uploadInput.type = "file";
            uploadInput.hidden = true;
            document.body.appendChild(uploadInput);
            uploadInput.click();
            uploadInput.onchange = function(e) {
                const files = e.target.files;
                if (!files.length) return;
                if (files.length > 1) {
                    return toast.error("Only one file allowed");
                }
                uploadFeatureImage({featureImage: files[0]});
            }
        } else {
            uploadInput.click();
        }
    }

    function handleIcon() { 
        if (!uploadIconInput) {
            uploadIconInput = document.createElement('input')
            uploadIconInput.type = "file";
            uploadIconInput.hidden = true;
            document.body.appendChild(uploadIconInput);
            uploadIconInput.click();
            uploadIconInput.onchange = function(e) {
                const files = e.target.files;
                if (!files.length) return;
                if (files.length > 1) {
                    return toast.error("Only one file allowed");
                }
                uploadIcon({icon: files[0]});
            }
        } else {
            uploadIconInput.click();
        }
    }*/

    function handleDiscard() {
    //     setSubdomainEdit('');
    //     setName('');
    //     setDescription('');
    //     setIcon('');
    //     setFeaturedImage('');
    //     setFacebook('');
    //     setTwitter('');
    //     setLinkedin('');
    //     setYoutube('');
    //     setInstagram('');
    //     setGithub('');

    }

    return <div className="settings-general">

        <div className="title">
            General Settings
        </div>

        {/*<GeneralLanguageSelector
            subdomain={subdomain}
            languages={languages} 
            currentLanguageId={currentLanguageId}
            onChange={setCurrentLanguageId}
        />*/}


        <DualSetting 
            title="Name"
            description="Name of your blog"
            right={
                <Input 
                    title={null}
                    type="text"
                    name="name"
                    value={variant.name}
                    onChange={value => updateBlogVariantValue('name', value, currentLanguageId)}
                />
            }
        />
        <DualSetting
            title="Description"
            description="A short description (or sub-title) for your blog"
            right={
                <Input 
                    title={null}
                    type="text"
                    name="description" 
                    value={variant.description}
                    onChange={value => updateBlogVariantValue('description', value, currentLanguageId)}
                />
            }
        />

        <DualSetting 
            title="Icon"
            description="The icon of your blog"
            right={
                <Input
                    title={null}
                    type="text"
                    name="icon"
                    value={blog.icon_url}
                    onChange={value => updateBlogValue('icon_url', value)}
                />
                // <div>
                /*<div class="image-head">
                    {/!* <img src="https://picsum.photos/200/200" alt="Avatar" className="image-center"/> *!/}
                    <img src={icon} alt="Avatar" className="image-center"/>
                    <button 
                        onClick={uploadIconAjax.status === 'loading' ? null: handleIcon}
                        className="button-style uploadButton button small inactive">
                        {uploadIconAjax.status === 'loading' ? "Uploading..." : 
                            <span>Upload <Upload /></span>
                        }
                    </button>
                </div>*/
            }
        />

        <DualSetting 
            title="Featured Image"
            description="A featured image for the homepage of your blog. Useful when sharing on social media"
            right={
                <Input
                    title={null}
                    type="text"
                    name="featured Image"
                    value={null}
                    onChange={() => {}}
                />

                /*<div class="image-head">
                    <img src={featuredImage} alt="Avatar" className="image-center"/>
                    <button 
                        onClick={uploadFeatureImageAjax.status === 'loading' ? null: handleFeatureImage}
                        className="button-style uploadButton button small inactive">
                            {   
                                uploadFeatureImageAjax.status === 'loading' ? "Uploading..." : 
                                    <span>Upload <Upload /></span> 
                            }
                    </button>
                </div>*/
            }
        />

        <DualSetting 
            title="Social Media"
            description="Links to your social media channels (use full URLs with https://)."
            right={null}
        />

        <div className="swift-settings social-media">
            <DualSetting
                title="Facebook"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="blog-facebook"
                        value={blog.social_facebook}
                        onChange={value => updateBlogValue('social_facebook', value)}
                    />
                }
            />
            <DualSetting 
                title="Twitter"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="blog-twitter"
                        value={blog.social_twitter}
                        onChange={value => updateBlogValue('social_twitter', value)}
                    />
                }
            />
            <DualSetting 
                title="Linkedin"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="blog-linkedin"
                        value={blog.social_linkedin}
                        onChange={value => updateBlogValue('social_linkedin', value)}
                    />
                }
            />
            <DualSetting 
                title="Youtube"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="blog-youtube"
                        value={blog.social_youtube}
                        onChange={value => updateBlogValue('social_youtube', value)}
                    />
                }
            />
            <DualSetting
                title="TikTok"
                right={
                    <Input
                        title={null}
                        type="text"
                        name="blog-tiktok"
                        value={blog.social_tiktok}
                        onChange={value => updateBlogValue('social_tiktok', value)}
                    />
                }
            />
            <DualSetting 
                title="Instagram"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="blog-instagram"
                        value={blog.social_instagram}
                        onChange={value => updateBlogValue('social_instagram', value)}
                    />
                }
            />
            <DualSetting 
                title="Github"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="github"
                        value={blog.social_github}
                        onChange={value => updateBlogValue('social_github', value)}
                    />
                }
            />
        </div>

        <SettingsSave
            keys={
                [
                    'subdomain',

                    'social_facebook',
                    'social_twitter',
                    'social_youtube',
                    'social_instagram',
                    'social_linkedin',
                    'social_tiktok',
                    'social_github',
                ]}
            variantKeys={
                [
                    'name',
                    'description'
                ]
            }
        />

    </div>

}