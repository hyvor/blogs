import React, {useEffect, useRef, useState} from 'react'
import { Popup, PopupHeaderDefault, PopupFooterSingleButton, PopupBodyDefault } from './Popup';

export const CODEMIRROR_MODES = {
    scss: { name: 'twig', base: 'text/x-scss'},
    twig: { name: 'twig', base: 'text/html' },
    js: 'text/javascript',
    yaml: 'text/x-yaml'
}

interface Props {

    /**
     * Send an ID if the same component is used for multiple different values/files
     * (for example in theme file editing)
     */
    id?: null | string | number,
    value: string,
    onChange: (val: string) => any,
    onSave?: (val: string) => any,
    extension: keyof typeof CODEMIRROR_MODES,

    props?: object
}

interface FullScreenProps {
    id?: null | string | number,
    initCm: (ref: any, cm: any) => any,
    setShowCodeFullScreen: (val: boolean) => any

    props?: object
}

const CodeMirrorEditorFullScreen = ({ id = null, initCm, setShowCodeFullScreen, props }: FullScreenProps) => {
    const codeFullScreenRef = useRef<null | HTMLDivElement>(null);
    const codeFullScreenCm = useRef<any>(null);

    useEffect(() => {
        initCm(codeFullScreenRef, codeFullScreenCm)
    }, []);

    return <div className='code-fullscreen'>
                <Popup
                    header={
                        <PopupHeaderDefault title={
                            <div>
                                Code Editor
                            </div>
                        } />
                    }
                    body={
                        <PopupBodyDefault>
                            <div {...props} className="global-codemirror-wrap" ref={codeFullScreenRef}/>
                        </PopupBodyDefault>
                    } 
                    footer={
                        <PopupFooterSingleButton
                            name="Close"
                            onClick={() => setShowCodeFullScreen(false)}
                        />
                    } 
                />
            </div>
}

export default function CodemirrorEditor({ id = null, value, onChange, onSave, extension, props = {} } : Props) {

    const codeRef = useRef<null | HTMLDivElement>(null);
    const codeCm = useRef<any>(null);
    const tabSize = extension === 'yaml' ? 2 : 4;

    const [showCodeFullScreen, setShowCodeFullScreen] = useState(false);

    function handleTab(cm: any) {
        if (cm.somethingSelected()) {
          cm.indentSelection("add");
        } else {
          cm.replaceSelection(cm.getOption("indentWithTabs")? "\t":
            Array(cm.getOption("indentUnit") + 1).join(" "), "end", "+input");
        }
    }

    function initCm(ref: any, cm: any) {

        (ref.current as HTMLDivElement).innerHTML = "";

        function handleSave() {
            onSave && onSave(cm.current.doc.getValue())
        }

        cm.current = (window as any).CodeMirror(ref.current, {
            value,
            mode: CODEMIRROR_MODES[extension],
            theme: 'solarized',
            keyMap: 'sublime',
            tabSize,
            indentWithTabs: false,
            indentUnit: tabSize,
            lineWrapping: false,
            lineNumbers: true,
            matchBrackets: true,
            matchTags: {bothTags: true},
            autoCloseBrackets: true,
            autoCloseTags: true,
            extraKeys: {
                "Ctrl-S": handleSave,
                "Cmd-S": handleSave,
                "Tab": handleTab
            }
        })
        cm.current.on('change', function() {
            const val = cm.current.doc.getValue();
            onChange(val);
        })

    }

    useEffect(() => {
        if (codeCm.current)
            return;
        initCm(codeRef, codeCm)
    }, []);

    useEffect(() => {
        initCm(codeRef, codeCm);
    }, [id, showCodeFullScreen])

    return <div>
            {showCodeFullScreen && 
                <CodeMirrorEditorFullScreen 
                    id={id}
                    initCm={initCm}
                    setShowCodeFullScreen={setShowCodeFullScreen}
                    props={props}/>
            }              
            <div {...props} className="global-codemirror-wrap" ref={codeRef} onClick={() => setShowCodeFullScreen(true)}/>
        </div>

}
