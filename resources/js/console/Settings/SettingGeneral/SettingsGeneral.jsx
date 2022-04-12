import React, { useState, useEffect} from 'react';
import { useActions, useValues } from 'kea';
import DualSetting from '../../ReusableComponents/DualSetting';
import Input from '../../ReusableComponents/Input';
import Select from '../../ReusableComponents/Select';
import SettingsSave from '../../ReusableComponents/SettingsSave';

import subdomainLogic from '../../logic/subdomainLogic';
import blogLogic from '../../logic/blogLogic';
import blogsLogic from '../../logic/blogsLogic';
import languagesLogic from '../../logic/languagesLogic';
import GeneralLanguageSelector from './GeneralLanguageSelector';



// Should find a way to set up the should save section in the pop-up.
export default function SettingsGeneral() {

    const subdomain = subdomainLogic.values.subdomain;
    const blogLogicBuilt = blogLogic({subdomain})
    const { blog } = useValues(blogLogicBuilt)
    const { updateData} = useActions(blogLogicBuilt)

    const { languages, getLanguageById } = useValues(languagesLogic({subdomain}))
    const { findBlogBySubdomain } = useValues(blogsLogic)
    const [currentLanguageId, setCurrentLanguageId] = useState( findBlogBySubdomain(subdomain).blog.default_language.id );
    const currentLanguage = getLanguageById(currentLanguageId);
    const variants = blog.variants || [];
    const variant = variants[currentLanguageId] || {};


    const [pointerEvent, setPointerEvent] = useState();

    // To display data in the pop-up
    const [variantName, setName] = useState();
    const [variantDescription, setDescription] = useState();
    const [subdomainEdit, setSubdomain] = useState('');
    const [icon, setIcon] = useState('');
    const [featuredImage, setFeaturedImage] = useState('');
    const [facebook, setFacebook] = useState('');
    const [twitter, setTwitter] = useState('');
    const [linkedIn, setLinkedin] = useState('');
    const [youtube, setYoutube] = useState('');
    const [instagram, setInstagram] = useState('');
    const [github, setGithub] = useState('');

    useEffect(() => {
        if (typeof(blog) !== 'undefined') {
            setSubdomain(blog.subdomain);
            setIcon(blog.social_facebook);
            setFeaturedImage(blog.social_facebook);
            setFacebook(blog.social_facebook);
            setTwitter(blog.social_twitter);
            setLinkedin(blog.social_linkedin);
            setYoutube(blog.social_youtube);
            setInstagram(blog.social_instagram);
            setGithub(blog.social_github);
            setName(variant.name)
            setDescription(variant.description)

        }
    },[blog])
    
    function handleSave(e) {
        e.preventDefault();
        updateData({
            subdomainEdit:subdomainEdit,
            name: variantName,
            description:variantDescription,
            icon:icon,
            featureImageId:featuredImage,
            social_facebook:facebook,
            social_twitter:twitter,
            social_linkedin:linkedIn,
            social_youtube: youtube,
            social_instagram:instagram,
            social_github:github
        });
    }

    // const [htWebsteId, setHtWebsiteId] = useState(null);
    // const shouldSave = subdomain !== "";
    function shouldSave() {
        console.log('should save')
        // const shouldSave
        // if(subdomain){
            //  return subdomainEdit !== ""
        // }
        // subdomain !== ""
        // name !== ""
        // description !== ""
        // icon !== ""
        // featuredImage !== ""
        // facebook !== ""
        // twitter !== ""
        // linkedIn !== ""
        // youtube !== ""
        // instagram !== ""
        // github !== ""
    }

    function handleDiscard() {
        setSubdomain('');
        setName('');
        setDescription('');
        setIcon('');
        setFeaturedImage('');
        setFacebook('');
        setTwitter('');
        setLinkedin('');
        setYoutube('');
        setInstagram('');
        setGithub('');

    }

    return <div className="settings-general">

        <div className="title">
            General Settings
        </div>
        <GeneralLanguageSelector 
            subdomain={subdomain}
            languages={languages} 
            currentLanguageId={currentLanguageId}
            onChange={setCurrentLanguageId}
        />

        <DualSetting 
            title="Subdomain" 
            description="Subdomain is used to uniquely identify your blog within Hyvor Blogs"
            right={
                <div>
                    <Input 
                        title={null}
                        type="text"
                        name="subdomain"
                        value={subdomainEdit}
                        onChange={setSubdomain}
                    />
                </div>
            }
        />

        <DualSetting 
            title="Name"
            description="Name of your blog"
            right={
                <Input 
                    title={null}
                    type="text"
                    name="name"
                    value={variantName}
                    onChange={setName}
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
                    value={variantDescription}
                    onChange={setDescription}
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
                    value={icon}
                    onChange={setIcon}
                />
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
                    value={featuredImage}
                    onChange={setFeaturedImage}
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
                        name="facebook"
                        value={facebook}
                        onChange={setFacebook}
                    />
                }
            />
            <DualSetting 
                title="Twitter"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="twitter"
                        value={twitter}
                        onChange={setTwitter}
                    />
                }
            />
            <DualSetting 
                title="Linkedin"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="linkedIn"
                        value={linkedIn}
                        onChange={setLinkedin}
                    />
                }
            />
            <DualSetting 
                title="Youtube"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="youtube"
                        value={youtube}
                        onChange={setYoutube}
                    />
                }
            />
            <DualSetting 
                title="Instagram"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="instagram"
                        value={instagram}
                        onChange={setInstagram}
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
                        value={github}
                        onChange={setGithub}
                    />
                }
            />
        </div>

        <SettingsSave 
            should={shouldSave}
            onSave={handleSave}
            onDiscard={handleDiscard}
        />

    </div>

}