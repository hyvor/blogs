import React from 'react'
import PropTypes from 'prop-types'
import Loader from './Loader'

export function Popup(props) {
    return <div className="popup-wrap">
        <div className="popup box-style">
            <div className="popup-header" style={props.headerStyle}>{props.header}</div>
            <div className="popup-body" style={props.bodyStyle}>{props.body}</div>
            <div className="popup-footer" style={props.footerStyle}>{props.footer}</div>
        </div>
    </div>
}
Popup.propTypes = {
    header: PropTypes.element.isRequired,
    body: PropTypes.element.isRequired,
    footer: PropTypes.element,
}

export function PopupBodyDefault(props) {
    return <div className="popup-body-default">{props.children}</div>
}

export function PopupHeaderDefault(props) {
    return <div className="popup-header-default">{props.title}</div>
}
PopupHeaderDefault.propTypes = {
    title: PropTypes.string.isRequired
}


export function PopupFooterSingleButton(props) {
    return <div className="popup-footer-single">
        <button className={"button " + props.buttonClass} onClick={props.onClick}>{props.name}</button>
    </div>
}
PopupFooterSingleButton.propTypes = {
    name: PropTypes.string.isRequired,
    onClick: PropTypes.func.isRequired
}

// assuming cancel and main buttons
export function PopupFooterDoubleButton( {
    onCancel, cancelName, onClick, 
    name,
    isLoading, loadingName,
    buttonClass = ''
} ) {
    return <div className={"popup-footer-double" + (isLoading ? " loading" : "")}>
        <button className="button text-only" onClick={onCancel}>{cancelName || "Cancel"}</button>
        <button className={"button " + buttonClass} onClick={onClick}>
            {isLoading ? loadingName : name}
            {
                isLoading ?
                <div className="footer-loader">
                    <Loader size={20} />
                </div> : null
            }
        </button>
    </div>
}


export function PopupConfirm( { title, text, name, buttonClass, onClick, onCancel } ) {
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
            />
        }
    />
}

export function PopupNotice( {title, text, name, onClick, buttonClass} ) {

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