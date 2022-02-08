import { useActions, useValues } from 'kea';
import React, {useState} from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import mediaLogic from '../logic/blogLogic';
import subdomainLogic from '../logic/subdomainLogic';
import Loader from '../ReusableComponents/Loader';

export default function SettingsCode() {

    const subdomain = subdomainLogic.values.subdomain;
    const codeLogicBuilt = mediaLogic({subdomain})
    const { code, loadAjax } = useValues(codeLogicBuilt)

    return <div className="settings-delete">
        {
            loadAjax.status === 'loading' ?
            <Loader padding={200}/> :
                <div>
                    <div className="title">
                        Custom Code
                    </div>
                     <div>
                       {code.length > 0 && (
                            <div>
                                {code.map(codeData => (
                                    <div>
                                        <UpdateCode code_head={codeData.custom_head} code_footer={codeData.custom_footer}/>
                                    </div>
                                ))}
                            </div>
                        )} 
                    </div> 
                </div>
        }
    </div>

}

function UpdateCode ({code_head, code_footer}){
    const subdomain = subdomainLogic.values.subdomain;
    const codeLogicBuilt = mediaLogic({subdomain})
    const { updateData } = useActions(codeLogicBuilt)
    const { updateDataAjax } = useValues(codeLogicBuilt)

    const [updateCodeData, setUpdate] = useState({
        codeHead: code_head,
        codeFooter: code_footer,
    })
    function handleCodeHead(e) {
        e.preventDefault();
        setUpdate({...updateCodeData, 
            codeHead: e.target.value
        })
    }
    function handleCodeFooter(e) {
        e.preventDefault();
        setUpdate({...updateCodeData, 
            codeFooter: e.target.value
        })
    }
    function updateCode(e){
        e.preventDefault();
        updateData({
            codeHead: updateCodeData.codeHead,
            codeFooter: updateCodeData.codeFooter,
        });
        window.location.reload(false);
    }
    return <div>
            <DualSetting 
                title="Header Code"
                description="This code will be placed right before the </head> tag. You can use this to add custom CSS and meta tags for the whole blog."
                right={
                    <textarea className="shortCodeVerticalTextArea" value={updateCodeData.codeHead} onChange={handleCodeHead}></textarea>
                } 
            /> 
            <DualSetting 
                title="Footer Code"
                description="This code will be placed right before the </body> tag. If you want to add custom Javascript code (ex: analytics), this is the best place to add it."
                right={
                    <textarea className="shortCodeVerticalTextArea" value={updateCodeData.codeFooter} onChange={(e)=> {handleCodeFooter(e)}}></textarea>
                }
            />
            <button className="button small shortCodeSave" onClick={(e)=> {updateCode(e)}}>Save</button>
    </div>
}