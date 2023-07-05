import React from 'react'
import { useState } from 'react';
import useUpdateEffect from '../../helpers/hooks/useUpdateEffect';
import { PopupConfirm } from './Popup';
import { useActions, useValues } from "kea";
import blogLogic from "../logic/blogLogic";
import Loader from "./Loader";
import { CheckCircle } from "react-bootstrap-icons";
import { Blog, BlogVariant } from "../types";
import { toast } from "react-toastify";
import getSubdomain from "../logic-helpers/subdomain";
import { useBlogActions, useBlogValues } from '../logic-helpers/blog';

export default function SettingsSave(
    { keys, variantKeys = [], blogSubdomain }:
        { keys: Array<keyof Blog>, variantKeys?: Array<keyof BlogVariant>, blogSubdomain?: string }
) {

    const [isDiscarding, setIsDiscarding] = useState(false);
    const [isUpdated, setIsUpdated] = useState(false);

    const subdomain = getSubdomain()

    const blogLogicInst = blogLogic({ subdomain })
    const { updateBlogAjax, getDiff, getVariantDiff } = useValues(blogLogicInst)
    const { updateBlog, discardChanges } = useActions(blogLogicInst)
    const { blog } = useBlogValues()
    const { updateBlogValue } = useBlogActions()

    const [status, setStatus] = useState<null | "loading" | "success" | "error">(null);

    function handleDiscard() {
        setIsDiscarding(true);
    }

    const should = getDiff(keys) || getVariantDiff(variantKeys) || (blogSubdomain && blogSubdomain !== blog.subdomain)

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
        } else if (updateBlogAjax.status === 'error') {
            toast.error(updateBlogAjax.error)
        }
    }, [updateBlogAjax.status])

    function handleSave() {

        const hasSubdomainChanged = blogSubdomain && blogSubdomain !== blog.subdomain
        if (hasSubdomainChanged) {
            updateBlogValue('subdomain', blogSubdomain);
        }
        updateBlog({
            keys,
            variantKeys,
            onUpdate: () => {
                if (hasSubdomainChanged) {
                    window.location.replace('/console/' + blogSubdomain + '/settings/hosting');
                }
            }
        })
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
                        <Loader />
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
