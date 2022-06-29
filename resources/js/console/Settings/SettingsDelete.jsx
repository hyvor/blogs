import React from 'react';
import DualSetting from '../ReusableComponents/DualSetting';

export default function SettingsDelete() {

    return <div className="settings-delete">

        <div className="title">
            Delete
        </div>

        <DualSetting 
            title="Reset"
            description="To completely delete all data (posts, users, tags, and media) of this blog. There is no way to restore data after resetting."
            right={
                <button className="button danger medium">Reset Blog</button>
            }
        />

        <DualSetting 
            title="Delete"
            description="To completely delete the blog with its data. Make sure to export data before performing this action. There is no way to recover a blog after deleting."
            right={
                <button className="button danger medium">Delete Blog</button>
            }
        />

    </div>

}