import React from 'react'
import {Controlled as CodeMirror} from '../../helpers/copied/react-codemirror2'

export const CODEMIRROR_MODES = {
    scss: { name: 'twig', base: 'text/x-scss'},
    twig: { name: 'twig', base: 'text/html' },
    js: 'text/javascript',
    yaml: 'text/x-yaml'
}

interface Props {
    value: string,
    onChange: (val: string) => any,
    mode: keyof typeof CODEMIRROR_MODES,
}

export default function CodemirrorEditor({ value, onChange, mode } : Props) {

    const tabSize = mode === CODEMIRROR_MODES.yaml ? 2 : 4;

    return <CodeMirror
        value={value}
        options={{
            theme: 'solarized',
            keyMap: 'sublime',
            mode: CODEMIRROR_MODES[mode],
            tabSize,
            indentWithTabs: true,
            indentUnit: tabSize,
            lineWrapping: true,
            lineNumbers: true,
            matchBrackets: true,
            matchTags: {bothTags: true},
            autoCloseBrackets: true,
            autoCloseTags: true,
        }}
        onBeforeChange={(_, __, value) => onChange(value)}
    />

}
