import React, {useState} from 'react';
import DualSetting from '../../ReusableComponents/DualSetting';
import DeleteBlogPopup from "./DeleteBlogPopup";
import {PopupConfirm, PopupNotice} from "../../ReusableComponents/Popup";
import ClearCachePopup from "./ClearCachePopup";

export default function Danger() {

    const [isCacheClearing, setIsCacheClearing] = useState(false);
    const [ isDeleting, setIsDeleting ] = useState(false);
    const [ hasDeleted, setHasDeleted ] = useState(false)

    return <div className="settings-danger">

        <div className="title">
            Danger
        </div>

        <DualSetting
            title="Clear Cache"
            description="To clear the cache of your blog. This will not delete any data."
            right={
                <button className="button medium" onClick={() => setIsCacheClearing(true)}>Clear Cache</button>
            }
        />

        <DualSetting 
            title="Delete Blog"
            description="To completely delete the blog with its data. Make sure to export data before performing this action. There is no way to recover a blog after deleting."
            right={
                <button
                    className="button danger medium"
                    onClick={() => setIsDeleting(true)}
                >Delete Blog</button>
            }
        />

        {
            isCacheClearing && <ClearCachePopup onClose={() => setIsCacheClearing(false)} />
        }

        { isDeleting && <DeleteBlogPopup
            onClose={() => setIsDeleting(false)}
            onDelete={() => {setIsDeleting(false); setHasDeleted(true)}}
        /> }
        {
            hasDeleted &&
                <PopupNotice
                    title="Blog Deletion Started"
                    text="We have started deleting your blog. It will take a little while depending on the size of data you have. Click OK to go to our homepage"
                    name="OK"
                    onClick={() => location.href = '/'}
                />
        }

    </div>

}