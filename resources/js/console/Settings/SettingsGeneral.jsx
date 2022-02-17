import React, { useState } from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import Input from '../ReusableComponents/Input';
import Select from '../ReusableComponents/Select';

export default function SettingsGeneral() {

    const commentsOptions = [
        { value: 'talk', label: 'Hyvor Talk' },
        { value: 'other', label: 'Other' },
    ];

    const [htWebsteId, setHtWebsiteId] = useState(null);

    return <div className="settings-delete">

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
                        value={htWebsteId}
                        onChange={setHtWebsiteId}
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
                    value={htWebsteId}
                    onChange={setHtWebsiteId}
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
                    name="name"
                    value={htWebsteId}
                    onChange={setHtWebsiteId}
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
                    name="name"
                    value={htWebsteId}
                    onChange={setHtWebsiteId}
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
                    name="name"
                    value={htWebsteId}
                    onChange={setHtWebsiteId}
                />
            }
        />

        <DualSetting 
            title="Social Media"
            description="Links to your social media channels (use full URLs with https://)."
            right={
                <div>
                    <DualSetting 
                        title="Facebook"
                        right={
                            <Input 
                                title={null}
                                type="text"
                                name="facebook"
                                value={htWebsteId}
                                onChange={setHtWebsiteId}
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
                                value={htWebsteId}
                                onChange={setHtWebsiteId}
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
                                value={htWebsteId}
                                onChange={setHtWebsiteId}
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
                                value={htWebsteId}
                                onChange={setHtWebsiteId}
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
                                value={htWebsteId}
                                onChange={setHtWebsiteId}
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
                                value={htWebsteId}
                                onChange={setHtWebsiteId}
                            />
                        }
                    />
                </div>
            }
        />


    </div>

}