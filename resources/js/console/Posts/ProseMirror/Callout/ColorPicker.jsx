import React, {useState} from 'react'
import { SketchPicker } from 'react-color';

export default function ColorPicker({ color, onChange, onClose, preset }) {
    
    const [c, setC] = useState(color);
    
    function handleChange(hex) {
        setC(hex)
        onChange(hex)
    }

    const cover = {
        position: 'fixed',
        top: '0px',
        right: '0px',
        bottom: '0px',
        left: '0px',
    }
    
    return <div className="color-picker-view">
        <div style={ cover } onClick={ onClose }/>
        <SketchPicker
            color={c}
            onChange={({hex}) => handleChange(hex)}
            presetColors={preset}
        />
    </div>
}
