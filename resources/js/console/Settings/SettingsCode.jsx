import React, { useState } from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import CodemirrorEditor, { CODEMIRROR_MODES } from '../ReusableComponents/CodemirrorEditor';

export default function SettingsCode() {

    const [headerCode, setHeaderCode] = useState("");
    const [footerCode, setFooterCode] = useState("");

    return <div className="settings-code">

        <div className="title">
            Custom Code
        </div>

        

        <DualSetting 
            title="Header Code"
            description={
                <div>
                    This HTML code will be placed right before the &lt;/head&gt; tag. You can use this to add custom CSS and meta tags for the whole blog. In addition to HTML, you can also use <a className="link" href="https://blogs.hyvor.test/docs/themes-overview#twig" target="_blank">Twig</a>, and has access to <a className="link" href="https://blogs.hyvor.test/docs/themes-overview#variables" target="_blank">scope variables</a>.
                </div>
            }
            right={
                <CodemirrorEditor 
                    mode={CODEMIRROR_MODES.twig}
                    value={headerCode}
                    onChange={setHeaderCode}
                />
            }
        />

        <DualSetting 
            title="Footer Code"
            description="This HTML code will be placed right before the </body> tag. If you want to add custom Javascript code (ex: analytics), this is the best place to add it. Similar to the header code, you can use Twig and variables."
            right={
                <CodemirrorEditor 
                    mode={CODEMIRROR_MODES.twig}
                    value={footerCode}
                    onChange={setFooterCode}
                />
            }
        />


    </div>

}