import React  from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import Input from '../ReusableComponents/Input';
import Radio from '../ReusableComponents/Radio';
import SettingsSave from '../ReusableComponents/SettingsSave';
import { useBlogActions, useBlogValues } from './useBlog';
import CodemirrorEditor, {CODEMIRROR_MODES} from "../ReusableComponents/CodemirrorEditor";

export default function Comments() {

    const { blog } = useBlogValues();
    const { updateBlogValue } = useBlogActions();

    function handleCommentsTypeChange(value: string) {
        updateBlogValue('comments_type', value);
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
                        If you like a privacy-first, easy-to-use commenting system, try <a href="https://talk.hyvor.com" className="link" target="_blank">Hyvor Talk</a>. Hyvor Blogs integrates with Hyvor Talk, making it easier to moderate your comments within the HB console.
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
                                    type="text"
                                    name="ht-website-id"
                                    value={blog.comments_ht_website_id}
                                    onChange={val => updateBlogValue('comments_ht_website_id', val)}
                                />
                            }
                        />

                        {

                            <DualSetting
                                title="Hyvor Talk API Key"
                                description="Paste the API key provided by Hyvor Talk to moderate comments within the HB Console."
                                right={
                                    <Input
                                        type="text"
                                        name="ht-website-id"
                                        value={blog.comments_ht_api_key}
                                        onChange={val => updateBlogValue('comments_ht_api_key', val)}
                                    />
                                }
                            />

                        }
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
                            <CodemirrorEditor
                                mode={CODEMIRROR_MODES.twig}
                                value={blog.comments_code}
                                onChange={(val: string) => updateBlogValue('comments_code', val)}
                            />
                        }
                        column={true}
                    />
                }
            </div>

            <DualSetting 
                title="Newsletter Signup Form Code"
                description="Paste the embed code provided by a email newsletter service here (for the sign up form)."
                right={
                    <CodemirrorEditor
                        mode={CODEMIRROR_MODES.twig}
                        value={blog.newsletter_code}
                        onChange={(val: string) => updateBlogValue('newsletter_code', val)}
                    />
                }
                column={true}
            />

        </div>

        <SettingsSave keys={
            [
                'comments_type',
                'comments_ht_website_id', 'comments_ht_api_key',
                'comments_code',
                'newsletter_code'
            ]
        } />

    </div>

}
