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

        <DualSetting 
            title="Comments"
            description={
                <div>
                    Paste the embed code provided by a commenting system here. If you like a privacy-first, easy-to-use commenting system, try <a href="https://talk.hyvor.com" className="link" target="_blank">Hyvor Talk</a> (starts at $5/month).
                </div>
            }
            right={
                <div>
                    <div>
                        <Select
                            options={commentsOptions}
                            defaultValue={commentsOptions[0]}
                        />
                    </div>
                    <textarea></textarea>

                    <Input 
                        title="Hyvor Talk Website ID"
                        type="text"
                        name="ht-website-id"
                        value={htWebsteId}
                        onChange={setHtWebsiteId}
                    />
                </div>
            }
        />

        <DualSetting 
            title="Newsletter Signup Form Code"
            description="Paste the embed code provided by a email newsletter service here (for the sign up form)."
            right={
                <textarea></textarea>
            }
        />


    </div>

}