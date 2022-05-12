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
                placeholder={props.placeholder}
                autoFocus={props.autoFocus || false}
                autoComplete={props.autoComplete}
                name={props.name}
                value={props.value} 
                onChange={(e) => props.onChange(e.target.value)}
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
    autoComplete: PropTypes.bool,
    placeholder: PropTypes.string,

    bottom: PropTypes.element,
}