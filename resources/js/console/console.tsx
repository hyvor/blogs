import React from 'react';
import { resetContext } from 'kea'
import { routerPlugin } from 'kea-router'
import Scene from './Scene';
import { ToastContainer } from 'react-toastify'

import './lib/codemirror/codemirror';

import 'react-toastify/dist/ReactToastify.css';
import 'prosemirror-codemark/dist/codemark.css';
import {createRoot} from "react-dom/client";
import { ajaxPlugin } from 'kea-ajax'

resetContext({
    plugins: [
        routerPlugin(),
        ajaxPlugin()
    ]
});

function App() {
    
    return <div>
        <Scene />
        <ToastContainer />
    </div>

}

const root = createRoot(document.getElementById("app")!)
root.render(<App />);
