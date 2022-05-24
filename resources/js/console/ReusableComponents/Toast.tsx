/**
 * Triggers toast() one time
 */

import {toast} from 'react-toastify'
import React, { Fragment, useEffect } from 'react'

type ToastProps = {
    text: string;
    type: 'success' | 'error',
    options?: object
};

export default function Toast({ text, type, options } : ToastProps) {

    if (type === 'error' && !text)
        text="Something went wrong";

    useEffect(() => {
        type ? toast[type](text, options) : toast(text, options);
    }, []);
    
    return <Fragment />

}