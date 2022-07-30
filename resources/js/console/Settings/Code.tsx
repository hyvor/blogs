import React, { useState } from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import CodemirrorEditor, { CODEMIRROR_MODES } from '../ReusableComponents/CodemirrorEditor';
import SettingsSave from '../ReusableComponents/SettingsSave';
import {useBlogActions, useBlogValues} from "../logic-helpers/blog";

export default function Code() {

    const { blog } = useBlogValues();
    const { updateBlogValue } = useBlogActions();

    return <div className="settings-code">

        <div className="title">
            Custom Code
        </div>
        
        <div>

            <DualSetting 
                title="Head Code"
                description={
                    <div>
                        This HTML code will be placed right before the &lt;/head&gt; tag. You can use this to add custom CSS and meta tags for the whole blog. In addition to HTML, you can also use <a className="link" href="/docs/themes-overview#twig" target="_blank">Twig</a>, and has access to <a className="link" href="/docs/themes-overview#variables" target="_blank">route variables</a>.
                    </div>
                }
                right={
                    <CodemirrorEditor
                        mode={'twig'}
                        value={blog.code_head || ''}
                        onChange={(v: string) => updateBlogValue('code_head', v)}
                    />
                }
                column={true}
            />

            <DualSetting 
                title="Foot Code"
                description="This HTML code will be placed right before the </body> tag. If you want to add custom Javascript code (ex: analytics), this is the best place to add it. You can use Twig and route variables."
                right={
                    <CodemirrorEditor
                        mode={'twig'}
                        value={blog.code_foot || ''}
                        onChange={(v: string) => updateBlogValue('code_foot', v)}
                    />
                }
                column={true}
            />

        </div>

        <SettingsSave keys={
            [
                'code_head',
                'code_foot'
            ]
        } />

    </div>

}
