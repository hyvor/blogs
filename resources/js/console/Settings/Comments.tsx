import React  from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import SettingsSave from '../ReusableComponents/SettingsSave';
import { useBlogActions, useBlogValues } from '../logic-helpers/blog';
import CodemirrorEditor, {CODEMIRROR_MODES} from "../ReusableComponents/CodemirrorEditor";

export default function Comments() {

    const { blog } = useBlogValues();
    const { updateBlogValue } = useBlogActions();

    return <div className="settings-delete">

        <div className="title">
            Comments  & Newsletter
        </div>

        <div>

            <DualSetting 
                title="Commenting System"
                description={
                    <div>
                        Paste the embed code from your commenting system here. If you like a privacy-first, easy-to-use commenting system, try <a href="https://talk.hyvor.com" className="link" target="_blank">Hyvor Talk</a>. You can use Twig <a className="link" href="/docs/themes-templates#variables" target="_blank">route variables</a> if needed.
                    </div>
                }
                right={
                    <CodemirrorEditor
                        extension={'twig'}
                        value={blog.comments_code || ''}
                        onChange={(val: string) => updateBlogValue('comments_code', val)}
                    />
                }
                column={true}
            />

            <DualSetting 
                title="Newsletter Signup Form Code"
                description="Paste the embed code provided by a email newsletter service here (for the sign up form)."
                right={
                    <CodemirrorEditor
                        extension='twig'
                        value={blog.newsletter_code || ''}
                        onChange={(val: string) => updateBlogValue('newsletter_code', val)}
                    />
                }
                column={true}
            />

        </div>

        <SettingsSave keys={
            [
                'comments_code',
                'newsletter_code'
            ]
        } />

    </div>

}
