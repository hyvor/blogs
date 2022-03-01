import React from 'react';

export default function Checkbox({label, checked, onChange}) {

    return <label className="global-checkbox">{label}
        <input type="checkbox" checked={checked} onChange={(e) => onChange(e.target.checked)} />
        <span></span>
    </label>

}