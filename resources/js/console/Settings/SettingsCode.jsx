import { useActions, useValues } from 'kea';
import React, {useState} from 'react';
import DualSetting from '../ReusableComponents/DualSetting';
import mediaLogic from '../logic/codeLogic';
import subdomainLogic from '../logic/subdomainLogic';
import Loader from '../ReusableComponents/Loader';



export default function SettingsCode() {

    const subdomain = subdomainLogic.values.subdomain;
    const codeLogicBuilt = mediaLogic({subdomain})
    const { code, loadAjax, uploadAjax } = useValues(codeLogicBuilt)
    const { upload } = useActions(codeLogicBuilt)

    // console.log(code);

    // console.log {code.custom_footer};

    // const [updateCodeData, setUpdate] = useState({
    //     codeHead: codeHead,
    //     codeFooter: codeFooter,
    // })
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
        console.log(updateCodeData.userId);
        updateData({
            // userId: updateCodeData.userId,
            codeHead: updateCodeData.codeHead,
            codeFooter: updateCodeData.codeHead,
        });
    }

    return <div className="settings-delete">
        {
            loadAjax.status === 'loading' ?
            <Loader padding={200}/> :
                <div>

                    <div className="title">
                        Custom Code {code.custom_footer}
                    </div>
        
                    <form onSubmit={(e)=> {updateCode(e)}}>
                        <DualSetting 
                            title="Header Code"
                            description="This code will be placed right before the </head> tag. You can use this to add custom CSS and meta tags for the whole blog."
                            right={
                                <textarea className="shortCodeVerticalTextArea"  value={code.custom_footer} onChange={(e)=> {handleCodeHead(e)}} placeholder={code.code_head}></textarea>
                            } 
                        /> 

                        <DualSetting 
                            title="Footer Code"
                            description="This code will be placed right before the </body> tag. If you want to add custom Javascript code (ex: analytics), this is the best place to add it."
                            right={
                                <textarea className="shortCodeVerticalTextArea" value={code.codeFooter} onChange={(e)=> {handleCodeFooter(e)}} placeholder={code.code_footer}></textarea>
                            }
                        />
                        <button type='button' className ="button small shortCodeSave">Save</button>
                    </form>
                </div>
        }

    </div>

}