import React from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import Select from '../ReusableComponents/Select';

export default function SettingsMigrate() {

    return <div style={{
        flex: 1,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: 24,
        color: "#777",
        height: "100%"
    }}>Coming soon!</div>

    const importOptions = [
        { value: 'wordpress', label: 'WordPress' },
        { value: 'blogger', label: 'Blogger' },
        { value: 'ghost', label: 'Ghost' },
        { value: 'medium', label: 'Medium' }
    ];

    return <div className="settings-delete">

        <div className="title">
            Import & Export
        </div>

        

        <DualSetting 
            title="Import"
            description={
                <div>
                    To import data from another platform. You can import posts, tags, and users, but not media. See the <a href="/docs/importing">importing</a> documentation for more details.
                </div>
            }
            right={
                <div>
                    <Select 
                        options={importOptions}
                        defaultValue={importOptions[0]}
                    />
                    <input type="file" name="import-file" />
                </div>
            }
        />

        <DualSetting 
            title="Export"
            description="To export data and media from this blog. Data will be exported as a single file in WordPress format. Media will be exported as a zip file containing a folder of all media files."
            right={
                <div>
                    <div>
                        <button className="button medium">Export Data</button>
                    </div>
                    <div style={{marginTop: 10}}>
                        <button className="button medium">Export Media</button>
                    </div>
                </div>
            }
        />

    </div>

}