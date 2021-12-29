import React from 'react'
import PropTypes from 'prop-types'

export function Popup(props) {
    return <div className="popup-wrap">
        <div className="popup box">
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
        <button className="button" onClick={props.onClick}>{props.name}</button>
    </div>
}
PopupFooterSingleButton.propTypes = {
    name: PropTypes.string.isRequired,
    onClick: PropTypes.func.isRequired
}