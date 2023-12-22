import React, {useState} from 'react'
import { SketchPicker } from 'react-color';

interface ColorPickerProps {
    color: string,
    onChange: (hex: string) => void
    onClose: Function,
    preset: string[]
}


export default function ColorPicker({ color, onChange, onClose, preset = [] } : ColorPickerProps) {
    
    const [c, setC] = useState(color);
    
    function handleChange(hex: string) {
        setC(hex)
        onChange(hex)
    }

    const cover = {
        position: 'fixed',
        top: '0px',
        right: '0px',
        bottom: '0px',
        left: '0px',
    } as React.CSSProperties
    
    return <div className="color-picker-view">
        <div style={ cover } onClick={ () => onClose() }/>
        <SketchPicker
            color={c}
            onChange={({hex} : {hex: string}) => handleChange(hex)}
            presetColors={preset}
        />
    </div>
}
