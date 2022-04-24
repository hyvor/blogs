import React, {Fragment} from 'react';
import DualSetting from "../ReusableComponents/DualSetting";
import Switch from "../ReusableComponents/Switch";
import Select from "../ReusableComponents/Select";
import {useBlogActions, useBlogValues} from "./useBlog";
import SettingsSave from "../ReusableComponents/SettingsSave";

export default function SettingsHighlight() {
    
    const { blog } = useBlogValues();
    const { updateBlogData } = useBlogActions();

    const keys = ['syntax_on', 'syntax_theme', 'syntax_line_numbers'];
    
    const themes = window.appConfig.syntax_themes.map(t => ({
        value: t, label: t
    }));

    return <div className="settings-highlight">

        <div className="title">
            Syntax Highlighting
        </div>

        <DualSetting
            title="Syntax Highlighting"
            description="Syntax highlighting for code blocks"
            right={
                <Switch
                    checked={blog.syntax_on}
                    onChange={checked => updateBlogData('syntax_on', checked)}
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
                                onChange={v => updateBlogData('syntax_theme', v.value)}
                            />
                        }
                    />

                    <DualSetting
                        title="Line Numbers"
                        description="Add line numbers to code blocks"
                        right={
                            <Switch
                                checked={blog.syntax_line_numbers}
                                onChange={checked => updateBlogData('syntax_line_numbers', checked)}
                            />
                        }
                    />
                    
                </Fragment> : null
        }

        <SettingsSave keys={keys} />

    </div>

}
