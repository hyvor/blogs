import React, {MouseEventHandler} from 'react'
import { ArrowClockwise, CheckCircle, ExclamationCircle } from 'react-bootstrap-icons';
import Spinner from './Spinner';

/**
 * status = stale|loading|success|error
 */

interface ActionsButtonProps {
    className?: string,
    status: 'stale' | 'loading' | 'success' | 'error',
    staleName: string,
    loadingName: string,
    successName: string,
    errorName: string,
    staleOnClick?: MouseEventHandler,
    successOnClick?: MouseEventHandler,
    errorOnClick? :MouseEventHandler
}

export default function ActionButton({
    className = '',
    status, 
    staleName, loadingName, successName, errorName,
    staleOnClick, successOnClick, errorOnClick
} : ActionsButtonProps) {

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