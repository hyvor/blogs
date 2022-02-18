import React, { useState } from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import Input from '../ReusableComponents/Input';
import Select from '../ReusableComponents/Select';

export default function SettingsComments() {

    const commentsOptions = [
        { value: 'talk', label: 'Hyvor Talk' },
        { value: 'other', label: 'Other' },
    ];

    const [htWebsteId, setHtWebsiteId] = useState(null);

    return <div className="settings-delete">

        <div className="title">
            Comments  & Newsletter
        </div>

        <div>

            <DualSetting 
                title="Commenting Provider"
                description={
                    <div>
                        If you like a privacy-first, easy-to-use commenting system, try <a href="https://talk.hyvor.com" className="link" target="_blank">Hyvor Talk</a> (starts at $5/month). Hyvor Blogs integrates with Hyvor Talk, making it easier to moderate your comments within the HB console.
                    </div>
                }
                right={
                    <Select
                        options={commentsOptions}
                        defaultValue={commentsOptions[0]}
                    />
                }
            />

            <div className="swift-settings">
                <DualSetting 
                    title="Hyvor Talk Website ID"
                    description="Paste the website ID provided by Hyvor Talk. This is used to make the comments embed work on your website."
                    right={
                        <Input 
                            title={null}
                            type="text"
                            name="ht-website-id"
                            value={htWebsteId}
                            onChange={setHtWebsiteId}
                        />
                    }
                />

                <DualSetting 
                    title="Hyvor Talk API Key"
                    description="Paste the API key provided by Hyvor Talk. This is used to fetch comments from the HB console, making it easier to moderate comments here, without having to visit the HT console."
                    right={
                        <Input 
                            title={null}
                            type="text"
                            name="ht-api-key"
                            value={htWebsteId}
                            onChange={setHtWebsiteId}
                        />
                    }
                />
            </div>

            <DualSetting 
                title="Newsletter Signup Form Code"
                description="Paste the embed code provided by a email newsletter service here (for the sign up form)."
                right={
                    <textarea className="input"></textarea>
                }
            />

        </div>


    </div>

}