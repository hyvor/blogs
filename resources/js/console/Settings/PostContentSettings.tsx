import React, {Fragment} from 'react';
import DualSetting from "../ReusableComponents/DualSetting";
import Switch from "../ReusableComponents/Switch";
import Select from "../ReusableComponents/Select";
import {useBlogActions, useBlogValues} from "../logic-helpers/blog";
import SettingsSave from "../ReusableComponents/SettingsSave";
import {appConfig} from "../helpers";

export default function PostContentSettings() {
    
    const { blog } = useBlogValues();
    const { updateBlogValue } = useBlogActions();
    
    const themes = appConfig().syntax_themes.map(t => ({
        value: t, label: t
    }));

    return <div className="settings-highlight">

        <div className="title">
            Post Content Settings
        </div>

        <DualSetting
            title="Syntax Highlighting"
            description="Syntax highlighting for code blocks"
            column={true}
            right={
                <div style={{marginLeft: 40}}>
                    <DualSetting
                        title="Enabled"
                        right={
                            <Switch
                                checked={blog.syntax_on}
                                onChange={checked => updateBlogValue('syntax_on', checked)}
                            />
                        }
                    />

                    {
                        blog.syntax_on ?

                            <Fragment>

                                <DualSetting
                                    title="Theme"
                                    description={
                                        <div>Select a theme. <a
                                            href="/docs/syntax-highlighting#themes"
                                            target="_blank"
                                            className="link"
                                        >Preview themes</a>
                                        </div>
                                    }
                                    right={
                                        <Select
                                            options={themes}
                                            defaultValue={themes.find(t => t.value === (blog.syntax_theme || 'nord'))}
                                            onChange={(v: any) => updateBlogValue('syntax_theme', v.value)}
                                        />
                                    }
                                />

                                <DualSetting
                                    title="Line Numbers"
                                    description="Add line numbers to code blocks"
                                    right={
                                        <Switch
                                            checked={blog.syntax_line_numbers}
                                            onChange={checked => updateBlogValue('syntax_line_numbers', checked)}
                                        />
                                    }
                                />

                            </Fragment> : null
                    }

                </div>
            }
        />

        <DualSetting
            title="Heading Anchors"
            description="Add anchors to headings with IDs"
            right={
                <Switch
                    checked={blog.heading_anchors}
                    onChange={checked => updateBlogValue('heading_anchors', checked)}
                />
            }
        />

        <SettingsSave keys={
            [
                'syntax_on',
                'syntax_theme',
                'syntax_line_numbers',
                'heading_anchors'
            ]
        } />

    </div>

}
