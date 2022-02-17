import React, { useState } from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import Input from '../ReusableComponents/Input';
import Switch from '../ReusableComponents/Switch';

export default function SettingsSEO() {

    const [indexing, setIndexing] = useState(true);

    return <div className="settings-delete">

        <div className="title">
            SEO
        </div>

        <DualSetting 
            title="Allow Indexing"
            description="Allow search engines to index your blog"
            right={
                <Switch 
                    checked={indexing}
                    onChange={setIndexing}
                />
            }
        />

        <DualSetting 
            title="Robots.txt"
            description={
                <div>
                    Add your robots.txt content here, <b>except sitemaps</b> (They are dynamically added to the bottom of robots.txt)
                </div>
            }
            right={
                <textarea className="input"></textarea>
            }
        />

    </div>

}