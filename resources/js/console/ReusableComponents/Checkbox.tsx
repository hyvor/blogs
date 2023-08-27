import React from 'react';

type CheckboxType = {
    label?: string;
    checked: boolean;
    onChange: Function;
    testId?: string;
}

export default function Checkbox({label, checked, onChange, testId} : CheckboxType) {

    return <label className="global-checkbox" data-testid={testId}>{label}
        <input
            type="checkbox"
            checked={checked}
            onChange={(e) => onChange(e.target.checked)}
        />
        <span/>
    </label>

}