import React from 'react'
import PropTypes from 'prop-types'

export default function Input(props) {

    return (
        <div className={"input-view" + (props.error ? " error" : "") }>
            <div className="input-top">
                <div className="input-title">{props.title}</div>
                {props.error ? <div className="input-error">{props.error }</div> : null }
                {!props.error && props.success ? <div className="input-success">{props.success}</div> : null }
            </div>
            <input 
                type={props.type} 
                autoFocus={props.autoFocus || false} 
                name={props.name}
                value={props.value} 
                onChange={props.onChange}
                onKeyDown={props.onKeyDown}
                onBlur={props.onBlur}
                onFocus={props.onFocus}
                maxLength={props.maxLength}
                id={"input-" + props.name}
                className="input"
            ></input>
            {props.bottom ? <div className="input-bottom">{props.bottom}</div> : null}
        </div>
    );

}

Input.propTypes = {

    title: PropTypes.oneOfType([PropTypes.string, PropTypes.element]).isRequired, 
    error: PropTypes.string,
    success: PropTypes.string,

    type: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired,
    value: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    onKeyDown: PropTypes.func,
    onBlur: PropTypes.func,
    onFocus: PropTypes.func,
    maxLength: PropTypes.number,
    autoFocus: PropTypes.bool,

    bottom: PropTypes.element,
}