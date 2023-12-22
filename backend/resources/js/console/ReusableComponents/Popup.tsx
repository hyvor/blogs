import React, {ReactNode} from 'react'
import Loader from './Loader'
import { OutsideClick } from './OutsideClick'

interface PopupProps {
    header?: ReactNode,
    body: ReactNode,
    footer?: ReactNode,
    isCenter?: boolean,

    className?: string

    onClose?: Function
}

export function Popup(props: PopupProps) {
    return <div className={"popup-wrap" +
            (props.isCenter ? " center" : "") +
            (props.className ? " " + props.className : "")
        }>
        <div className="popup box-style">    
            <OutsideClick onClick={() => props.onClose?.()}>
                {props.header && <div className="popup-header">{props.header}</div>}
                <div className="popup-body">{props.body}</div>
                {props.footer && <div className="popup-footer">{props.footer}</div>}
            </OutsideClick>
        </div>
    </div>
}

export function PopupBodyDefault(props: {children: ReactNode}) {
    return <div className="popup-body-default">{props.children}</div>
}

export function PopupHeaderDefault(props: {title: ReactNode}) {
    return <div className="popup-header-default">{props.title}</div>
}

export function PopupFooterSingleButton(
    props:
    {buttonClass?: string, onClick: Function, name: ReactNode}
) {
    return <div className="popup-footer-single">
        <button className={"button " + props.buttonClass} onClick={e => props.onClick(e)}>{props.name}</button>
    </div>
}

interface PopupFooterDoubleButtonProps {

    isLoading?: boolean,
    name: ReactNode,
    loadingName?: ReactNode,
    buttonClass?: string,

    onClick: Function
    cancelName?: string,
    onCancel: Function
}

// assuming cancel and main buttons
export function PopupFooterDoubleButton( {
    onCancel, cancelName, onClick, 
    name,
    isLoading, loadingName,
    buttonClass = ''
} : PopupFooterDoubleButtonProps ) {
    return <div className={"popup-footer-double" + (isLoading ? " loading" : "")}>
        <button className="button medium text-only" onClick={() => onCancel()}>{cancelName || "Cancel"}</button>
        <button className={"button medium " + buttonClass} onClick={() => onClick()}>
            {isLoading ? (loadingName || "Loading") : name}
            {
                isLoading ?
                <div className="footer-loader">
                    <Loader size="mini" />
                </div> : null
            }
        </button>
    </div>
}

interface PopupConfirmProps {
    title: ReactNode,
    text: ReactNode,
    name: ReactNode,
    buttonClass?: string,
    onClick: Function,
    onCancel: Function,
    isLoading?: boolean,
    loadingName?: ReactNode
}

export function PopupConfirm( { title, text, name, buttonClass, onClick, onCancel, isLoading = false, loadingName } : PopupConfirmProps ) {
    return <Popup 
        header={<PopupHeaderDefault title={title} />}
        body={
            <PopupBodyDefault>{text}</PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                name={name}
                onClick={onClick}
                onCancel={onCancel}
                buttonClass={buttonClass}
                isLoading={isLoading}
                loadingName={loadingName}
            />
        }
    />
}

interface PopupNoticeProps {
    title: ReactNode,
    text: ReactNode,
    name: ReactNode,
    onClick: Function,
    buttonClass?: string
}

export function PopupNotice( {title, text, name, onClick, buttonClass} : PopupNoticeProps ) {

    return <Popup 
        header={<PopupHeaderDefault title={title} />}
        body={
            <PopupBodyDefault>{text}</PopupBodyDefault>
        }
        footer={
            <PopupFooterSingleButton name={name} onClick={onClick} buttonClass={buttonClass} />
        }
    />
}