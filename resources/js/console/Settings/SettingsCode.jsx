import React from 'react';
import DualSetting from '../ReusableComponents/DualSetting';

export default function SettingsCode() {

    return <div className="settings-delete">

        <div className="title">
            Custom Code
        </div>

        

        <DualSetting 
            title="Header Code"
            description="This code will be placed right before the </head> tag. You can use this to add custom CSS and meta tags for the whole blog."
            right={
                <textarea></textarea>
            }
        />

        <DualSetting 
            title="Footer Code"
            description="This code will be placed right before the </body> tag. If you want to add custom Javascript code (ex: analytics), this is the best place to add it."
            right={
                <textarea></textarea>
            }
        />


    </div>

}