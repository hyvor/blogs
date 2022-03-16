import React, { useState } from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import Input from '../ReusableComponents/Input';
import Select from '../ReusableComponents/Select';
import SettingsSave from '../ReusableComponents/SettingsSave';


// Should find a way to set up the should save section in the pop-up.
export default function SettingsGeneral() {

    // const commentsOptions = [
    //     { value: 'talk', label: 'Hyvor Talk' },
    //     { value: 'other', label: 'Other' },
    // ];

    const [subdomain, setSubdomain] = useState('');
    const [name, setName] = useState('');
    const [description, setDescription] = useState('');
    const [icon, setIcon] = useState('');
    const [featuredImage, setFeaturedImage] = useState('');
    const [facebook, setFacebook] = useState('');
    const [twitter, setTwitter] = useState('');
    const [linkedin, setLinkedin] = useState('');
    const [youtube, setYoutube] = useState('');
    const [instagram, setInstagram] = useState('');
    const [github, setGithub] = useState('');


    // const [htWebsteId, setHtWebsiteId] = useState(null);

    // const shouldSave = subdomain !== "";
    
    
    function shouldSave() {
        // console.log('hello')
        // const shouldSave
        // if(subdomain){
             return subdomain !== ""
        // }
        // subdomain !== ""
        // name !== ""
        // description !== ""
        // icon !== ""
        // featuredImage !== ""
        // facebook !== ""
        // twitter !== ""
        // linkedin !== ""
        // youtube !== ""
        // instagram !== ""
        // github !== ""
    }

    function handleSave() {
        
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

        <DualSetting 
            title="Subdomain" 
            description="Subdomain is used to uniquely identify your blog within Hyvor Blogs"
            right={
                <div>
                    <Input 
                        title={null}
                        type="text"
                        name="subdomain"
                        value={subdomain}
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
                    value={name}
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
                    value={description}
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
                        name="linkedin"
                        value={linkedin}
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