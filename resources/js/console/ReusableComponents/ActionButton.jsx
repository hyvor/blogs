import React from 'react'
import { ArrowClockwise, CheckCircle, ExclamationCircle } from 'react-bootstrap-icons';
import Spinner from './Spinner';

/**
 * status = stale|loading|success|error
 */
export default function ActionButton({
    className = '',
    status, 
    staleName, loadingName, successName, errorName,
    staleOnClick = null, successOnClick = null, errorOnClick = null
}) {

    let name;
    let icon;
    let cls = '';
    let onClick;

    if (status === 'stale') {
        name = staleName;
        onClick = staleOnClick;
    } else if (status === 'loading') {
        name = loadingName;
        icon = <Spinner size={10} />
    } else if (status === 'success') {
        icon = <CheckCircle />
        name = successName;
        cls = 'green';
        onClick = successOnClick;
    } else if (status === 'error') {
        icon = <ExclamationCircle />
        name = errorName;
        cls = 'danger';
        onClick = errorOnClick
    }

    return <button
        onClick={onClick}
        className={"button " + cls + " " + className}
    >
        {icon}{icon ? <span>&nbsp;&nbsp;</span> : null}{name}
    </button>

}