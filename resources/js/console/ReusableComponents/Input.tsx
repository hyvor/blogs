import React, {FocusEventHandler, KeyboardEventHandler} from 'react'

interface InputProps {

    value: any,
    onChange: (value: any) => void,

    type?: string,
    placeholder?: string,
    autoFocus?: boolean,
    autoComplete?: string,
    name?: string,
    onKeyDown?: KeyboardEventHandler<HTMLInputElement>,
    onBlur?: FocusEventHandler<HTMLInputElement>,
    onFocus?: FocusEventHandler<HTMLInputElement>,
    maxLength?: number,

    bottom?: React.ReactNode,
    error?: string | null,
    success?: string | null,
    title?: React.ReactNode

}

export default function Input(
    {

        // input props
        value,
        onChange,

        type = 'text',
        placeholder,
        autoFocus = false,
        autoComplete,
        name,
        onKeyDown,
        onBlur,
        onFocus,
        maxLength,

        // other
        bottom,
        error,
        success,
        title

    }
    : InputProps) {

    return (
        <div className={"input-view" + (error ? " error" : "") }>
            <div className="input-top">
                <div className="input-title">{title}</div>
                {error ? <div className="input-error">{error}</div> : null }
                {!error && success ? <div className="input-success">{success}</div> : null }
            </div>
            <input 
                type={type}
                placeholder={placeholder}
                autoFocus={autoFocus || false}
                autoComplete={autoComplete}
                name={name}
                value={value || ''}
                onChange={(e) => onChange(e.target.value)}
                onKeyDown={onKeyDown}
                onBlur={onBlur}
                onFocus={onFocus}
                maxLength={maxLength}
                id={"input-" + name}
                className="input"
            />
            {bottom ? <div className="input-bottom">{bottom}</div> : null}
        </div>
    );

}