import React from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import Switch from '../ReusableComponents/Switch';
import Callout, {CalloutColors} from "../ReusableComponents/Callout";
import {ExclamationCircle} from "react-bootstrap-icons";
import {useBlogActions, useBlogValues} from "../logic-helpers/blog";
import CodemirrorEditor, {CODEMIRROR_MODES} from "../ReusableComponents/CodemirrorEditor";
import SettingsSave from "../ReusableComponents/SettingsSave";
import Radio from "../ReusableComponents/Radio";

export default function SEO() {
    
    const { blog } = useBlogValues();
    const { updateBlogValue } = useBlogActions();
    
    function handleExternalLinkFollowChange(value: string) {
        updateBlogValue("seo_external_links_follow", value);
    }
    
    return <div className="settings-seo">

        <div className="title">
            SEO
        </div>

        <DualSetting
            title="Allow Indexing"
            description="Allow search engines to index your blog"
            right={
                <div>
                    <Switch
                        checked={blog.seo_indexing}
                        onChange={checked => updateBlogValue('seo_indexing', checked)}
                    />
                    {
                        !blog.seo_indexing ?
                        <Callout
                            icon={<ExclamationCircle />}
                            color={CalloutColors.ORANGE}
                            title="Search engines won't index your blog!"
                            text="A 'nofollow' meta tag is added to the header of every page in your blog, which prevents your blog from appearing on search results."
                        /> : null }
                </div>
            }
        />

        <DualSetting
            title="External Links Type"
            description="Should search engines follow external links in your posts?"
            right={
                <div>
                    <Radio
                        name="external-links-type"
                        placeholder="Follow"
                        value="follow"
                        onChange={handleExternalLinkFollowChange}
                        checkFor={blog.seo_external_links_follow}
                    />
                    <Radio
                        name="external-links-type"
                        placeholder="Nofollow"
                        value="nofollow"
                        onChange={handleExternalLinkFollowChange}
                        checkFor={blog.seo_external_links_follow}
                    />
                </div>
            }
        />

        <DualSetting
            title="Robots.txt"
            description={
                <div>
                    Customize the robots.txt file.
                </div>
            }
            right={
                <CodemirrorEditor
                    extension="twig"
                    value={blog.seo_robots_txt || ''}
                    onChange={(val: string) => updateBlogValue('seo_robots_txt', val)}
                />
            }
            column={true}
        />

        <SettingsSave
            keys={
                [
                    'seo_indexing', 'seo_robots_txt',
                    'seo_external_links_follow'
                ]
            }
        />

    </div>

}
