import React, { useState, useRef} from 'react';
import { useActions, useValues } from 'kea';
import DualSetting from '../../ReusableComponents/DualSetting';
import Input from '../../ReusableComponents/Input';
import SettingsSave from '../../ReusableComponents/SettingsSave';

import subdomainLogic from '../../logic/subdomainLogic';
import blogLogic from '../../logic/blogLogic';
import languagesLogic from '../../logic/languagesLogic';
import {BlogVariant, Language} from "../../types";
import LanguageSelector from "../../ReusableComponents/LanguageSelector";
import ImageSelector from "../../ReusableComponents/ImageSelector";
import getSubdomain from "../../logic-helpers/subdomain";


// Should find a way to set up the should save section in the pop-up.
export default function SettingsGeneral() {

    const subdomain = getSubdomain();
    const blogLogicBuilt = blogLogic({subdomain})
    const { blog } = useValues(blogLogicBuilt)
    const { updateBlogValue, updateBlogVariantValue, createVariant } = useActions(blogLogicBuilt)

    const { primaryLanguage, getLanguageById } = useValues(languagesLogic({subdomain}))
    const [currentLanguageId, setCurrentLanguageId] = useState( primaryLanguage.id );
    const currentLanguage = getLanguageById(currentLanguageId) as Language
    const variant = blog.variants.find(v => v.language_id === currentLanguageId) || {} as BlogVariant;

    return <div className="settings-general">

        <div className="title">
            General Settings
        </div>

        <LanguageSelector
            id={0}
            languageId={currentLanguageId}
            variantsLanguageIds={blog.variants.map(v => v.language_id)}
            onChange={(languageId) => setCurrentLanguageId(languageId)}
            variantCreator={createVariant}
        />

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

        <div className={!currentLanguage.is_primary ? "global-non-primary-language-hidden" : ""}>

            <DualSetting
                title="Logo"
                description="The logo of your blog"
                right={
                    <div className="image-head">
                        <ImageSelector
                            src={blog.logo_url}
                            onChange={url => updateBlogValue('logo_url', url)}
                        />
                    </div>
                }
            />

            <DualSetting
                title="Cover Image"
                description="A cover image for the blog. Some themes may not display this."
                right={
                    <ImageSelector
                        src={blog.cover_url}
                        onChange={(url) => updateBlogValue('cover_url', url)}
                    />
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

                    'logo_url',
                    'cover_url'
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