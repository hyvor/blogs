import React, { Fragment, ReactNode, useEffect, useRef, useState } from 'react';

import SettingsGeneral from '../Settings/General/SettingsGeneral';
import Import from './Import/Import';
import { getUserBlogBlog } from '../logic-helpers/blog';
import SettingsLink from '../ReusableComponents/SettingsLink';
import SettingsSelect from '../ReusableComponents/SettingsSelect';
import Export from './Export/Export';
import LinkAnalysisTool from './LinkAnalysis/LinkAnalysisTool';
import { Download, Plus, PlusCircle } from 'react-bootstrap-icons';


export default function Tools({ type }: { type: string | undefined }) {
    const [pannel, setPannel] = useState(type || 'import');
    let pannelOption = [
        { value: 'import', label: 'Import' },
        { value: 'export', label: 'Export' },
    ]

    let Type = () => <SettingsGeneral />;
    switch (pannel) {
        case 'import':
            Type = () => <Import />;
            break;
        case 'export':
            Type = () => <Export />;
            break;
        case 'link-analysis':
            Type = () => <LinkAnalysisTool />
            break;
        default:
            Type = () => <Import />;
    }

    return <div className="posts-view settings-view">
        <div className="box box-left">

            <div className="settings-nav">
                <SettingsLink path="" name="Import" pannelName={'import'} setPannel={setPannel} toolsPrefix={true} icon={<PlusCircle />}/>
                <SettingsLink path="/export" name="Export" pannelName={'export'} setPannel={setPannel} toolsPrefix={true} icon={<Download />}/>

                <div />

                <SettingsLink
                    path="/link-analysis"
                    name="Link Analysis"
                    pannelName="link-analysis"
                    setPannel={setPannel}
                    toolsPrefix={true}
                />

            </div>
        </div>
        <div className="box box-right settings-right">
            <div className='settings-selector'>
                <div className='title'>Tools</div>
                <SettingsSelect name="" value={pannel} options={pannelOption} setPannel={setPannel} />
            </div>

            <Type />
        </div>
    </div>

}
