import React  from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import SettingsSave from '../ReusableComponents/SettingsSave';
import { useBlogActions, useBlogValues } from '../logic-helpers/blog';
import CodemirrorEditor, {CODEMIRROR_MODES} from "../ReusableComponents/CodemirrorEditor";
import NavLink from "../ReusableComponents/NavLink";

export default function Comments() {

    const { blog } = useBlogValues();
    const { updateBlogValue } = useBlogActions();

    return <div className="settings-delete">

        <div className="title">
            Comments  & Newsletter
        </div>

        <div>

            <DualSetting 
                title="Comments Embed Code"
                description={
                    <div>
                        Paste the embed code from your commenting system here. You can use Twig <a className="link" href="/docs/themes-templates#variables" target="_blank">route variables</a> if needed. To connect Hyvor Talk for FREE, go to <NavLink 
                                href={`/console/${blog.subdomain}/integrations/hyvor-talk`}
                                className="link"
                            >Integrations &rarr; Hyvor Talk</NavLink>.
                    </div>
                }
                right={
                    <CodemirrorEditor
                        extension={'twig'}
                        value={blog.comments_code || ''}
                        onChange={(val: string) => updateBlogValue('comments_code', val)}
                        allowFullScreen={true}
                        fileName='Comments Embed Code'
                    />
                }
                column={true}
            />

            <DualSetting 
                title="Newsletter Signup Form Code"
                description={
                    <div>
                        Paste the embed code provided by a email newsletter service here (for the sign up form). You can use Twig <a className="link" href="/docs/themes-templates#variables" target="_blank">route variables</a> if needed.
                    </div>
                }
                right={
                    <CodemirrorEditor
                        extension='twig'
                        value={blog.newsletter_code || ''}
                        onChange={(val: string) => updateBlogValue('newsletter_code', val)}
                        allowFullScreen={true}
                        fileName='Newsletter Signup Form Code'
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
