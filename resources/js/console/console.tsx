import React from 'react';
import ReactDOM from 'react-dom';
import { resetContext, Provider } from 'kea'
import { routerPlugin } from 'kea-router'
import Scene from './Scene';
import { ajaxPlugin } from './lib/kea-plugins/ajax';
import { ToastContainer } from 'react-toastify'

import './lib/codemirror/codemirror';

import 'react-toastify/dist/ReactToastify.css';
import 'prosemirror-codemark/dist/codemark.css';
import {createRoot} from "react-dom/client";

resetContext({
    plugins: [
        routerPlugin(),
        ajaxPlugin()
    ]
});

function App() {
    
    return <Provider>
        <Scene />
        <ToastContainer />
    </Provider>

}

const root = createRoot(document.getElementById("app"))
root.render(<App />);
