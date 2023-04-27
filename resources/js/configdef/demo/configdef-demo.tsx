import React, { useState } from "react";
import { createRoot } from "react-dom/client";
import '../../../css/console/console.scss';
import './demo.scss';
import CodemirrorEditor from "../../console/ReusableComponents/CodemirrorEditor";
import '../../console/lib/codemirror'
import ConfigDef from "../ConfigDef";

const root = createRoot(document.getElementById('app')!);
root.render(<App />);

function App() {

    const [config, setConfig] = useState("");
    const [configDef, setConfigDef] = useState("");

    return <div className="demo-view">

        <div className="column">
            <div className="column-title">
                config.yaml
            </div>
            <div className="column-content">
                <div className="box">
                    <CodemirrorEditor 
                        value={config}
                        onChange={value => setConfig(value)}
                        extension="yaml"
                    />
                </div>
            </div>
        </div>

        <div className="column">
            <div className="column-title">
                config.def.yaml
            </div>
            <div className="column-content">
                <div className="box">
                    <CodemirrorEditor 
                        value={configDef}
                        onChange={value => setConfigDef(value)}
                        extension="yaml"
                    />
                </div>
            </div>
        </div>

        <div className="column">
            <div className="column-title">
                User Interface
            </div>
            <div className="column-content">
                <div className="box">
                    <ConfigDef
                        configYaml={config}
                        configDefYaml={configDef}
                    />
                </div>
            </div>
        </div>

    </div>
}