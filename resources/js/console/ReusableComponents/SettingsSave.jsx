import React from 'react'
import { useState } from 'react';
import useUpdateEffect from '../../helpers/hooks/useUpdateEffect';
import { PopupConfirm } from './Popup';
import {useActions, useValues} from "kea";
import subdomainLogic from "../logic/subdomainLogic";
import blogLogic from "../logic/blogLogic";
import Loader from "./Loader";
import {CheckCircle} from "react-bootstrap-icons";

export default function SettingsSave({ keys }) {

    const [isDiscarding, setIsDiscarding] = useState(false);
    const [isUpdated, setIsUpdated] = useState(false);
    
    const { subdomain } = useValues(subdomainLogic);

    const blogLogicInst = blogLogic({subdomain})
    const { saveAjax, getDiff } = useValues(blogLogicInst)
    const { save, discardChanges } =  useActions(blogLogicInst)
    
    const [status, setStatus] = useState(null);

    function handleDiscard() {
        setIsDiscarding(true);
    }
    
    const should = getDiff(keys)

    useUpdateEffect(() => {
        setIsUpdated(true);
    }, [should])
    
    useUpdateEffect(() => {
        setStatus(saveAjax.status)
        if (saveAjax.status === 'success') {
            setIsUpdated(false);
            setTimeout(() => {
                setStatus(null)
            }, 2000);
        }
    }, [saveAjax.status])
    
    function handleSave() {
        save({keys})
    }
    function handleDiscardConfirm() {
        discardChanges(keys)
        setIsDiscarding(false);
    }

    return <div>

        <div className={"settings-save " + 
            (should || status === 'success' ? "open" : 
                (isUpdated ? "close" : "start"))}
        >
            {
                status === 'loading' ?
                    <div>
                        <Loader size={35} />
                    </div> : 
                    status === 'success' ?
                        <CheckCircle size={25} />
                        : <div>
                            <button
                                onClick={handleDiscard}
                                className="button text-only"
                            >Discard</button>
                            <button
                                className="button"
                                onClick={handleSave}
                            >SAVE</button>
                        </div>
            }
        </div>

        {
            isDiscarding ?
            <PopupConfirm 
                title="Discard Changes"
                text="Are you sure you want to discard current changes?"
                name="Discard"
                onClick={handleDiscardConfirm}
                onCancel={() => setIsDiscarding(false)}
                buttonClass="danger"
            /> : null
        }
        
    </div>

}
