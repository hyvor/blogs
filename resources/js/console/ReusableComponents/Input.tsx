import React, {FocusEventHandler, KeyboardEventHandler, ReactNode} from 'react'

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
    readOnly?: boolean,

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
        readOnly,

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
                <label className="input-title" htmlFor={"input-" + name}>{title}</label>
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
                readOnly={readOnly}
                id={"input-" + name}
                className="input"
            />
            {bottom ? <div className="input-bottom">{bottom}</div> : null}
        </div>
    );

}

interface InputViewProps {
    title: ReactNode,
    content: ReactNode
}

export function InputView({ title, content }: InputViewProps) {

    return <div className="input-view">
        <div className="input-top">
            <div className="input-title">{ title }</div>
        </div>
        { content }
    </div>

}