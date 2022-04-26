import React, { useState } from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import Input from '../ReusableComponents/Input';
import Radio from '../ReusableComponents/Radio';

export default function SettingsHosting() {

    const [hostedAt, setHostedAt] = useState('subdomain');

    function handleHostedAtChange(e) {
        setHostedAt(e.target.value);
    }

    return <div className="settings-delete">

        <div className="title">
            Hosting
        </div>

        <DualSetting 
            title="Hosting on"
            description="Where do you like to host your blog?"
            right={
                <div>
                    <div>
                        <Radio 
                            name="hosted-at"
                            placeholder="Subdomain" 
                            value="subdomain" 
                            onChange={handleHostedAtChange}
                            checkFor={hostedAt}
                        />
                        <Radio 
                            name="hosted-at"
                            placeholder="Custom Domain (CNAME)" 
                            value="domain" 
                            onChange={handleHostedAtChange}
                            checkFor={hostedAt}
                        />
                        <Radio
                            name="hosted-at"
                            placeholder="Self-hosting" 
                            value="self"
                            onChange={handleHostedAtChange}
                            checkFor={hostedAt}
                        />
                    </div>
                    <p className="global-description">
                        Your blog will be hosted at <b>test.hyvorblogs.io</b>.
                    </p>
                </div>
            }
        />

        {
            hostedAt === 'domain' ?

            <DualSetting 
                title="Custom Domain"
                description="Custom domain "
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="custom-domain"
                        value={""}
                        onChange={null}
                    />
                }
            /> : null

        }

        {

            hostedAt === 'self' ?

            <DualSetting 
                title="Self-hosting URL"
                description="Set the absolute URL where you are self-hosting your blog."
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="self-hosting-url"
                        value={""}
                        onChange={null}
                    />
                }
            /> : null

        }

    </div>

}