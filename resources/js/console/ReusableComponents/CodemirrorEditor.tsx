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

export default function CodemirrorEditor({ id = null, value, onChange, onSave, extension, props = {} } : Props) {

    const ref = useRef<null | HTMLDivElement>(null);
    const cm = useRef<any>(null);
    const tabSize = extension === 'yaml' ? 2 : 4;

    const fullScreenRef = useRef<null | HTMLDivElement>(null);
    const fullScreencm = useRef<any>(null);

    const [showCodeFullScreen, setShowCodeFullScreen] = useState(false);

    function handleTab(cm: any) {
        if (cm.somethingSelected()) {
          cm.indentSelection("add");
        } else {
          cm.replaceSelection(cm.getOption("indentWithTabs")? "\t":
            Array(cm.getOption("indentUnit") + 1).join(" "), "end", "+input");
        }
    }

    function initCm() {
        console.log('Init cm');

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
            const val = cm.current.doc.getValue()
            onChange(val)
        })

    }

    useEffect(() => {
        if (cm.current)
            return;
        initCm()
    }, []);

    useEffect(() => {
        if (!showCodeFullScreen)
            return;
        initCm()
    }, [id, showCodeFullScreen])

    const CodeFullScreen = () => {
        console.log('Render full screen');
        return <div>
                TEEEEEEEST
                <div {...props} className="global-codemirror-wrap" ref={ref}/>
            </div>
        /*<Popup 
            header={
                <PopupHeaderDefault title={
                    <div>
                        Code Editor
                    </div>
                } />
                }
                body={
                    <PopupBodyDefault>
                        <div {...props} className="global-codemirror-wrap" ref={ref}/>
                    </PopupBodyDefault>
                } 
                footer={
                    <PopupFooterSingleButton
                        name="Close"
                        onClick={() => setShowCodeFullScreen(false)}
                    />
                } />*/
    }
    console.log('Ref:', ref);
    console.log('Cm:', cm)
    return <div>
            {showCodeFullScreen && <CodeFullScreen />}
             <div {...props} className="global-codemirror-wrap" ref={ref}/>
        </div>

}
