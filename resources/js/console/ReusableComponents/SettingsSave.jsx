import React from 'react'
import { useState } from 'react';
import useUpdateEffect from '../../helpers/hooks/useUpdateEffect';
import { PopupConfirm } from './Popup';

export default function SettingsSave({ should, onSave, onDiscard }) {

    const [isDiscarding, setIsDiscarding] = useState(false);
    const [isUpdated, setIsUpdated] = useState(false);


    function handleDiscard() {
        setIsDiscarding(true);
    }

    useUpdateEffect(() => {
        setIsUpdated(true);
    }, [should])

    return <div>

        <div className={"settings-save " + (should ? "open" : (isUpdated ? "close" : "start"))}>
            <button
                onClick={handleDiscard}
                className="button text-only"
            >Discard</button>
            <button 
                className="button"
                onClick={onSave}
            >SAVE</button>
        </div>

        {
            isDiscarding ?
            <PopupConfirm 
                title="Discard Changes"
                text="Are you sure you want to discard current changes?"
                name="Discard"
                onClick={onDiscard}
                onCancel={() => setIsDiscarding(false)}
                buttonClass="danger"
            /> : null
        }
        
    </div>

}