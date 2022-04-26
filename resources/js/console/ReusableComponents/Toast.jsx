/**
 * Triggers toast() one time
 */

import {toast} from 'react-toastify'
import React, { Fragment, useEffect } from 'react'

export default function Toast({ text, type, options }) {

    if (type === 'error' && !text)
        text="Something went wrong";

    useEffect(() => {
        type ? toast[type](text, options) : toast(text, options);
    }, []);
    
    return <Fragment />

}