import React from 'react'
import { useState } from 'react';
import useUpdateEffect from '../../helpers/hooks/useUpdateEffect';
import { PopupConfirm } from './Popup';
import {useActions, useValues} from "kea";
import subdomainLogic from "../logic/subdomainLogic";
import blogLogic from "../logic/blogLogic";
import Loader from "./Loader";
import {CheckCircle} from "react-bootstrap-icons";
import {Blog, BlogVariant} from "../types";

export default function SettingsSave(
    { keys, variantKeys = [] } :
    { keys: Array<keyof Blog>, variantKeys: Array<keyof BlogVariant> }
) {

    const [isDiscarding, setIsDiscarding] = useState(false);
    const [isUpdated, setIsUpdated] = useState(false);
    
    const { subdomain } = useValues(subdomainLogic);

    const blogLogicInst = blogLogic({subdomain})
    const { updateBlogAjax, getDiff, getVariantDiff } = useValues(blogLogicInst)
    const { updateBlog, discardChanges } =  useActions(blogLogicInst)
    
    const [status, setStatus] = useState(null);

    function handleDiscard() {
        setIsDiscarding(true);
    }
    
    const should = getDiff(keys) || getVariantDiff(variantKeys)

    useUpdateEffect(() => {
        setIsUpdated(true);
    }, [should])
    
    useUpdateEffect(() => {
        setStatus(updateBlogAjax.status)
        if (updateBlogAjax.status === 'success') {
            setIsUpdated(false);
            setTimeout(() => {
                setStatus(null)
            }, 2000);
        }
    }, [updateBlogAjax.status])
    
    function handleSave() {
        updateBlog({keys, variantKeys})
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
