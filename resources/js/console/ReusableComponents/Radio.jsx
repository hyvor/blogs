import React from 'react';

export default function Radio(props) {
    
    return <div className="global-radio-wrap">
        <label className="radio-container">
            {props.placeholder ?  <span className="placeholder">{props.placeholder}</span> : null}
            <input
                type="radio" 
                name={props.name} 
                value={props.value} 
                onChange={props.onChange} 
                checked={props.checkFor === props.value}   
                disabled={props.disabled || false} 
            />
            <span className="checkmark"></span>
        </label>
    </div>

}