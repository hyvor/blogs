import React from 'react'
import {Controlled as CodeMirror} from 'react-codemirror2'

export const CODEMIRROR_MODES = {
    scss: 'text/x-scss',
    twig: { name: 'twig', base: 'text/html' },
    js: 'text/javascript'
}

export default function CodemirrorEditor({ value, onChange, mode }) {

    return <CodeMirror
        value={value}
        options={{
            theme: 'solarized',
            keyMap: 'sublime',
            tabSize: 4,
            indentWithTabs: true,
            indentUnit: 4,
            mode,
            lineWrapping: true,
            lineNumbers: true,
        }}
        onBeforeChange={(_, __, value) => onChange(value)}
    />

}