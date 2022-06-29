import React from 'react';

type CheckboxType = {
    label?: string;
    checked: boolean;
    onChange: Function;
}

export default function Checkbox({label, checked, onChange} : CheckboxType) {

    return <label className="global-checkbox">{label}
        <input
            type="checkbox"
            checked={checked}
            onChange={(e) => onChange(e.target.checked)} />
        <span/>
    </label>

}