import React from 'react';
import DualSetting from "../ReusableComponents/DualSetting";
import Radio from "../ReusableComponents/Radio";
import SettingsSave from "../ReusableComponents/SettingsSave";
import {useBlogActions, useBlogValues} from "./useBlog";

export default function SettingsColorMode() {
    
    const { blog } = useBlogValues();
    const { updateBlogValue } = useBlogActions();

    const keys = ['color_modes', 'color_mode_default'];
    
    function handleColorModeChange(e) {
        updateBlogValue("color_modes", e.target.value);
    }
    function handleColorModeDefaultChange(e) {
        updateBlogValue("color_mode_default", e.target.value);
    }
    
    return <div className="settings-color-mode">

        <div className="title">
            Light & Dark Modes
        </div>

        <DualSetting
            title="Color Mode(s)"
            description="What color modes do you want in your blog?"
            right={
                <div>
                    <Radio
                        name="color-modes"
                        placeholder="Both"
                        value="both"
                        onChange={handleColorModeChange}
                        checkFor={blog.color_modes}
                    />
                    <Radio
                        name="color-modes"
                        placeholder="Light"
                        value="light"
                        onChange={handleColorModeChange}
                        checkFor={blog.color_modes}
                    />
                    <Radio
                        name="color-modes"
                        placeholder="Dark"
                        value="dark"
                        onChange={handleColorModeChange}
                        checkFor={blog.color_modes}
                    />
                </div>
            }
        />

        {
            blog.color_modes === 'both' ?

                <DualSetting
                    title="Default Color Mode"
                    description="Which color mode should users see by default?"
                    right={
                        <div>
                            <Radio
                                name="color-mode-default"
                                placeholder="User's Operating System (OS) Preference"
                                value="os"
                                onChange={handleColorModeDefaultChange}
                                checkFor={blog.color_mode_default}
                            />
                            <Radio
                                name="color-mode-default"
                                placeholder="Light"
                                value="light"
                                onChange={handleColorModeDefaultChange}
                                checkFor={blog.color_mode_default}
                            />
                            <Radio
                                name="color-mode-default"
                                placeholder="Dark"
                                value="dark"
                                onChange={handleColorModeDefaultChange}
                                checkFor={blog.color_mode_default}
                            />
                        </div>
                    }
                /> : null
        }
        
        <SettingsSave keys={keys} />
        
    </div>
    
}
