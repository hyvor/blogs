import { useActions, useValues } from 'kea';
import React, { useState } from 'react';
import blogLogic from '../logic/blogLogic';
import subdomainLogic from '../logic/subdomainLogic';
import DualSetting from '../ReusableComponents/DualSetting';
import Input from '../ReusableComponents/Input';
import Radio from '../ReusableComponents/Radio';
import Select from '../ReusableComponents/Select';
import SettingsSave from '../ReusableComponents/SettingsSave';
import { useBlogActions, useBlogValues } from './useBlog';

export default function SettingsComments() {

    const { blog, getDiff } = useBlogValues();
    const { updateBlogData, save, setToOriginal } = useBlogActions();

    const [htWebsteId, setHtWebsiteId] = useState(null);

    function handleCommentsTypeChange(e) {
        updateBlogData('comments_type', e.target.value);
    }

    return <div className="settings-delete">

        <div className="title">
            Comments  & Newsletter
        </div>

        <div>

            <DualSetting 
                title="Commenting System"
                description={
                    <div>
                        If you like a privacy-first, easy-to-use commenting system, try <a href="https://talk.hyvor.com" className="link" target="_blank">Hyvor Talk</a> (starts at $5/month). Hyvor Blogs integrates with Hyvor Talk, making it easier to moderate your comments within the HB console.
                    </div>
                }
                right={
                    <div>
                        <Radio
                            name="comments-type"
                            placeholder="Hyvor Talk" 
                            value="ht" 
                            onChange={handleCommentsTypeChange}
                            checkFor={blog.comments_type}
                        />
                        <Radio 
                            name="comments-type"
                            placeholder="Other" 
                            value="other" 
                            onChange={handleCommentsTypeChange}
                            checkFor={blog.comments_type}
                        />
                    </div>
                }
            />

            <div className="swift-settings">

                {
                    blog.comments_type === 'ht' ?
                    <div>
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
                :
                    <DualSetting 
                        title="Comments Embed Code"
                        description={
                            <div>
                                Paste the embed code provided by your commenting system provider. You can use Twig <a className="link" href="/docs/themes-overview#variables" target="_blank">scope variables</a> if needed.
                            </div>
                        }
                        right={
                            <textarea className="input"></textarea>
                        }
                    />
                }
            </div>

            <DualSetting 
                title="Newsletter Signup Form Code"
                description="Paste the embed code provided by a email newsletter service here (for the sign up form)."
                right={
                    <textarea className="input"></textarea>
                }
            />

        </div>

        <SettingsSave
            should={getDiff !== null}
            onSave={save}
            onDiscard={setToOriginal}
        />

    </div>

}