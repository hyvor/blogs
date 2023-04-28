import React, {ReactNode} from 'react';

interface RadioProps {
    placeholder?: ReactNode,
    name: string,
    value: string,
    onChange: (value: string) => void,
    checkFor: string,
    disabled?: boolean
}

export default function Radio(props: RadioProps) {
    
    return <div className="global-radio-wrap" data-testid="radio">
        <label className="radio-container">
            {props.placeholder ?  <span className="placeholder">{props.placeholder}</span> : null}
            <input
                type="radio" 
                name={props.name} 
                value={props.value} 
                onChange={(e) => props.onChange(e.target.value)}
                checked={props.checkFor === props.value}   
                disabled={props.disabled || false} 
            />
            <span className="checkmark"/>
        </label>
    </div>

}